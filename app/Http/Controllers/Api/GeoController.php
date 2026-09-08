<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeoController extends Controller
{
    public function search(Request $request)
    {
        if (! filter_var(config('services.geo.enabled', true), FILTER_VALIDATE_BOOL)) {
            return response()->json([]);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }
        $q = Str::of($q)->substr(0, 200)->toString();
        $biasLat = $request->query('lat');
        $biasLon = $request->query('lon');

        $key = 'geo:v3:search:'.md5($q.'|'.$biasLat.'|'.$biasLon);

        return Cache::remember($key, now()->addMinutes(30), function () use ($q, $biasLat, $biasLon) {
            try {
                $photon = $this->photonSearch($q, $biasLat, $biasLon);
                $nomi = $this->nominatimSearch($q);
                $merged = $this->mergeSearch($photon, $nomi, $q);
                return $merged;
            } catch (\Throwable $e) {
                Log::warning('[Geo] search: '.$e->getMessage());
                return [];
            }
        });
    }

    public function reverse(Request $request)
    {
        if (! filter_var(config('services.geo.enabled', true), FILTER_VALIDATE_BOOL)) {
            return response()->json(null, 204);
        }

        $lat = (float) $request->query('lat');
        $lon = (float) $request->query('lon');
        if (! $lat && ! $lon) {
            return response()->json(null, 400);
        }

        $key = 'geo:v3:rev:'.round($lat, 5).':'.round($lon, 5);

        $payload = Cache::remember($key, now()->addMinutes(20), function () use ($lat, $lon) {
            $out = $this->photonReverse($lat, $lon) ?: $this->nominatimReverse($lat, $lon);
            return $out ?: false;
        });

        if ($payload === false || $payload === null) {
            return response()->json(null, 204);
        }

        return response()->json($payload);
    }

    private function ua(): string
    {
        $email = (string) config('services.geo.email', '');

        return 'EstiloDorado/1.1 (Laravel API)'.($email ? " <$email>" : '');
    }

    private function timeout(): int
    {
        return max(6, (int) config('services.geo.timeout', 8));
    }

    private function http()
    {
        return Http::withHeaders(['User-Agent' => $this->ua(), 'Accept-Language' => 'es'])
            ->withOptions([
                'verify' => (bool) config('services.geo.verify', true),
                'timeout' => $this->timeout(),
            ]);
    }

    private function photonSearch(string $q, mixed $lat, mixed $lon): array
    {
        $params = ['q' => $q, 'limit' => 8, 'lang' => 'es'];
        if (is_numeric($lat) && is_numeric($lon)) {
            $params['lat'] = (float) $lat;
            $params['lon'] = (float) $lon;
        }
        $res = $this->http()->get('https://photon.komoot.io/api/', $params);
        if (! $res->successful()) {
            return [];
        }
        $out = [];
        foreach (($res->json('features') ?? []) as $f) {
            $g = $f['geometry']['coordinates'] ?? null;
            $p = $f['properties'] ?? [];
            if (! is_array($g) || count($g) < 2) {
                continue;
            }
            $out[] = [
                'lat' => (string) $g[1],
                'lon' => (string) $g[0],
                'display_name' => $this->photonDisplay($p),
                'class' => $p['osm_key'] ?? '',
                'type' => $p['osm_value'] ?? '',
                'address' => [
                    'road' => $p['street'] ?? $p['name'] ?? '',
                    'house_number' => $p['housenumber'] ?? '',
                    'city' => $p['city'] ?? '',
                    'suburb' => $p['district'] ?? $p['locality'] ?? '',
                    'state' => $p['state'] ?? '',
                    'country' => $p['country'] ?? '',
                ],
            ];
        }

        return $out;
    }

    private function nominatimSearch(string $q): array
    {
        $base = rtrim((string) config('services.geo.base', 'https://nominatim.openstreetmap.org'), '/');
        $params = [
            'format' => 'json',
            'limit' => 5,
            'addressdetails' => 1,
            'q' => $q,
            'countrycodes' => 'pe',
            'accept-language' => 'es',
        ];
        $email = (string) config('services.geo.email', '');
        if ($email) {
            $params['email'] = $email;
        }
        $res = $this->http()->get($base.'/search', $params);
        if (! $res->successful()) {
            return [];
        }

        return is_array($res->json()) ? $res->json() : [];
    }

    private function mergeSearch(array $photon, array $nomi, string $q): array
    {
        $all = array_merge($photon, $nomi);
        usort($all, function ($a, $b) use ($q) {
            return $this->scoreHit($b, $q) <=> $this->scoreHit($a, $q);
        });
        $seen = [];
        $out = [];
        foreach ($all as $it) {
            $k = round((float) ($it['lat'] ?? 0), 4).':'.round((float) ($it['lon'] ?? 0), 4);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = $it;
            if (count($out) >= 5) {
                break;
            }
        }

        return $out;
    }

    private function scoreHit(array $it, string $q): int
    {
        $score = 0;
        $cls = strtolower((string) ($it['class'] ?? ''));
        $type = strtolower((string) ($it['type'] ?? $it['addresstype'] ?? ''));
        $display = mb_strtolower((string) ($it['display_name'] ?? ''));
        $road = mb_strtolower((string) (($it['address']['road'] ?? '')));
        if (in_array($cls, ['highway', 'building', 'place', 'amenity'], true)) {
            $score += 6;
        }
        if (in_array($type, ['residential', 'house', 'building', 'road', 'living_street', 'unclassified', 'primary', 'secondary'], true)) {
            $score += 8;
        }
        $qLow = mb_strtolower($q);
        foreach (preg_split('/[\s,]+/u', $qLow) as $tok) {
            if (mb_strlen($tok) < 3) {
                continue;
            }
            if (str_contains($display, $tok) || str_contains($road, $tok)) {
                $score += 4;
            }
        }
        $score += (int) round(((float) ($it['importance'] ?? 0)) * 10);

        return $score;
    }

    private function photonReverse(float $lat, float $lon): ?array
    {
        try {
            $res = $this->http()->get('https://photon.komoot.io/reverse', [
                'lat' => $lat,
                'lon' => $lon,
                'lang' => 'es',
            ]);
            if (! $res->successful()) {
                return null;
            }
            $f = ($res->json('features') ?? [])[0] ?? null;
            if (! $f) {
                return null;
            }
            $p = $f['properties'] ?? [];

            return $this->normalizeReverse(
                $p['street'] ?? $p['name'] ?? '',
                $p['housenumber'] ?? '',
                $p['state'] ?? '',
                $p['city'] ?? $p['county'] ?? '',
                $p['district'] ?? $p['locality'] ?? $p['city'] ?? '',
                $this->photonDisplay($p)
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nominatimReverse(float $lat, float $lon): ?array
    {
        try {
            $base = rtrim((string) config('services.geo.base', 'https://nominatim.openstreetmap.org'), '/');
            $params = [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lon,
                'addressdetails' => 1,
                'zoom' => 18,
                'accept-language' => 'es',
            ];
            $email = (string) config('services.geo.email', '');
            if ($email) {
                $params['email'] = $email;
            }
            $res = $this->http()->get($base.'/reverse', $params);
            if (! $res->successful()) {
                return null;
            }
            $data = $res->json();
            $a = $data['address'] ?? [];
            $via = $a['road'] ?? $a['pedestrian'] ?? $a['residential'] ?? $a['path'] ?? $a['neighbourhood'] ?? '';
            $numero = $a['house_number'] ?? '';
            $dep = $a['state'] ?? $a['region'] ?? '';
            $prov = $a['county'] ?? $a['state_district'] ?? $a['city'] ?? '';
            $dist = $a['city_district'] ?? $a['suburb'] ?? $a['town'] ?? $a['village'] ?? $a['neighbourhood'] ?? '';

            return $this->normalizeReverse($via, $numero, $dep, $prov, $dist, $data['display_name'] ?? '');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function photonDisplay(array $p): string
    {
        $parts = array_filter([
            trim(($p['street'] ?? $p['name'] ?? '').' '.($p['housenumber'] ?? '')),
            $p['district'] ?? null,
            $p['city'] ?? null,
            $p['state'] ?? null,
            $p['country'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    private function normalizeReverse(string $via, string $numero, string $dep, string $prov, string $dist, string $display): array
    {
        $via = trim($via);
        $numero = trim($numero);
        if ($via === '' && $display !== '') {
            $head = trim(explode(',', $display)[0] ?? '');
            if ($head !== '') {
                if (preg_match('/^(.*?)[\s,]+(\d+[A-Za-z\-]*)\s*$/u', $head, $m)) {
                    $via = trim($m[1]);
                    $numero = $numero !== '' ? $numero : $m[2];
                } else {
                    $via = $head;
                }
            }
        }
        if ($numero === '' && $display !== '') {
            $head = trim(explode(',', $display)[0] ?? '');
            if (preg_match('/(\d{1,6}[A-Za-z\-]*)/', $head, $m)) {
                $numero = $m[1];
            }
        }

        return [
            'via' => $via,
            'numero' => $numero,
            'departamento' => $dep,
            'provincia' => $prov,
            'distrito' => $dist,
            'display' => $display ?: null,
        ];
    }
}
