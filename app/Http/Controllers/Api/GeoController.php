<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GeoController extends Controller
{
    public function search(Request $request)
    {
        if (!filter_var(config('services.geo.enabled', true), FILTER_VALIDATE_BOOL)) {
            return response()->json([]); // desactivado por env
        }

        $q = trim((string)$request->query('q', ''));
        if ($q === '') return response()->json([]);

        $q = Str::of($q)->substr(0, 200)->__toString();
        // "Av. X, 28" → "Av. X 28" (Nominatim suele ignorar el número si va tras coma)
        $q = preg_replace('/,\s*(\d{1,5}[A-Za-z]?)\b/', ' $1', $q) ?? $q;

        $cacheMin = (int)config('services.geo.cache_min', 1440);
        $key = 'geo:nominatim:v2:' . md5($q);

        return Cache::remember($key, now()->addMinutes($cacheMin), function () use ($q) {
            try {
                $base   = rtrim(config('services.geo.base', 'https://nominatim.openstreetmap.org'), '/');
                $verify = (bool)config('services.geo.verify', true);
                $timeout= (int)config('services.geo.timeout', 5);
                $email  = (string)config('services.geo.email', '');
                $ua     = 'EstiloDorado/1.0 (Laravel API)'.($email ? " <$email>" : '');

                $params = [
                    'format'          => 'jsonv2',
                    'limit'           => 8,
                    'addressdetails'  => 1,
                    'q'               => $q,
                    'countrycodes'    => 'pe',
                    'dedupe'          => 1,
                ];
                if ($email) $params['email'] = $email;

                $res = Http::withHeaders(['User-Agent' => $ua])
                    ->withOptions(['verify' => $verify, 'timeout' => $timeout])
                    ->get("$base/search", $params);

                if ($res->successful()) {
                    $hits = $res->json();
                    if (! is_array($hits)) {
                        return [];
                    }

                    return self::rankHits($hits, $q);
                }

                // Si hay 429/503 u otro, devolvemos vacío pero sin romper UX
                Log::warning('[Geo] respuesta no exitosa de Nominatim', [
                    'status' => $res->status(), 'q' => $q
                ]);
                return [];
            } catch (\Throwable $e) {
                Log::warning('[Geo] fallo geocoding: '.$e->getMessage());
                return [];
            }
        });
    }

    public function reverse(Request $request)
{
    if (!filter_var(config('services.geo.enabled', true), FILTER_VALIDATE_BOOL)) {
        return response()->json(null, 204);
    }

    $lat = (float) $request->query('lat');
    $lon = (float) $request->query('lon');
    if (!$lat && !$lon) return response()->json(null, 400);

    try {
        $base   = rtrim(config('services.geo.base', 'https://nominatim.openstreetmap.org'), '/');
        $verify = (bool)config('services.geo.verify', true);
        $timeout= (int)config('services.geo.timeout', 5);
        $email  = (string)config('services.geo.email', '');
        $ua     = 'EstiloDorado/1.0 (Laravel API)'.($email ? " <$email>" : '');

        $params = [
            'format'         => 'json',
            'lat'            => $lat,
            'lon'            => $lon,
            'addressdetails' => 1,
            'zoom'           => 20, // más granular para captar número
        ];
        if ($email) $params['email'] = $email;

        $res = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => $ua])
            ->withOptions(['verify' => $verify, 'timeout' => $timeout])
            ->get("$base/reverse", $params);

        if (!$res->successful()) return response()->json(null, 204);

        $data = $res->json();
        $a = $data['address'] ?? [];

        $via = $a['road'] ?? $a['pedestrian'] ?? $a['residential'] ?? $a['path'] ?? '';
        $numero = $a['house_number'] ?? '';
        $departamento = $a['state'] ?? $a['region'] ?? '';
        $provincia    = $a['county'] ?? $a['state_district'] ?? $a['city'] ?? '';
        $distrito     = $a['city_district'] ?? $a['suburb'] ?? $a['town'] ?? $a['village'] ?? $a['neighbourhood'] ?? '';

        return response()->json([
            'via'          => $via,
            'numero'       => $numero,
            'departamento' => $departamento,
            'provincia'    => $provincia,
            'distrito'     => $distrito,
            'display'      => $data['display_name'] ?? null,
        ]);
    } catch (\Throwable $e) {
        \Log::warning('[Geo reverse] '.$e->getMessage());
        return response()->json(null, 204);
    }
}

    /** @param list<array<string,mixed>> $hits */
    private static function rankHits(array $hits, string $q): array
    {
        $wantNum = '';
        if (preg_match('/\b(\d{1,5}[A-Za-z]?)\b/', $q, $m)) {
            $wantNum = strtoupper($m[1]);
        }
        usort($hits, function ($a, $b) use ($wantNum) {
            return self::scoreHit($b, $wantNum) <=> self::scoreHit($a, $wantNum);
        });

        return array_values($hits);
    }

    /** @param array<string,mixed> $h */
    private static function scoreHit(array $h, string $wantNum): float
    {
        $s = ((float) ($h['importance'] ?? 0)) * 12;
        $addr = is_array($h['address'] ?? null) ? $h['address'] : [];
        $hn = strtoupper((string) ($addr['house_number'] ?? ''));
        if ($wantNum !== '') {
            if ($hn !== '' && $hn === $wantNum) {
                $s += 80;
            } elseif ($hn !== '' && str_contains($hn, $wantNum)) {
                $s += 30;
            }
        }
        $cls = (string) ($h['class'] ?? '');
        $type = (string) ($h['type'] ?? '');
        if (in_array($type, ['house', 'building', 'yes'], true) || $cls === 'building') {
            $s += 12;
        }
        if ($cls === 'highway' && $hn === '') {
            $s -= 8;
        }

        return $s;
    }
}
