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
    /** Photon no acepta lang=es (solo default, de, en, fr). */
    private const PHOTON_LANG = 'en';

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

        $key = 'geo:v4:search:'.md5($q.'|'.$biasLat.'|'.$biasLon);
        $cached = Cache::get($key);
        if (is_array($cached) && $cached !== []) {
            return response()->json($cached);
        }

        try {
            $merged = [];
            foreach ($this->queryVariants($q) as $variant) {
                $nomi = $this->nominatimSearch($variant, $biasLat, $biasLon);
                $photon = $this->photonSearch($variant, $biasLat, $biasLon);
                $merged = $this->mergeSearch(array_merge($nomi, $photon), $variant);
                if ($merged !== []) {
                    break;
                }
            }
            if ($merged !== []) {
                Cache::put($key, $merged, now()->addMinutes(20));
            }

            return response()->json($merged);
        } catch (\Throwable $e) {
            Log::warning('[Geo] search: '.$e->getMessage());

            return response()->json([]);
        }
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

        $key = 'geo:v4:rev:'.round($lat, 5).':'.round($lon, 5);
        $cached = Cache::get($key);
        if (is_array($cached) && ! empty($cached['display'] ?? $cached['via'] ?? null)) {
            return response()->json($cached);
        }

        $out = $this->nominatimReverse($lat, $lon) ?: $this->photonReverse($lat, $lon);
        if (! $out) {
            return response()->json(null, 204);
        }
        Cache::put($key, $out, now()->addMinutes(20));

        return response()->json($out);
    }

    private function ua(): string
    {
        $email = (string) config('services.geo.email', 'contacto@estilodorado.net.pe');

        return 'EstiloDorado/1.2 (https://estilodorado.net.pe; '.$email.')';
    }

    private function timeout(): int
    {
        return max(8, (int) config('services.geo.timeout', 8));
    }

    private function http()
    {
        return Http::withHeaders(['User-Agent' => $this->ua(), 'Accept-Language' => 'es'])
            ->withOptions([
                'verify' => (bool) config('services.geo.verify', true),
                'timeout' => $this->timeout(),
            ]);
    }

    /** Av./Jr. + quita "Lima, Lima, Lima". */
    private function queryVariants(string $q): array
    {
        $q = trim(preg_replace('/\s+/u', ' ', $q) ?? $q);
        $expanded = $q;
        $map = [
            '/\bAvda\.?\b/iu' => 'Avenida',
            '/\bAv\.?\b/iu' => 'Avenida',
            '/\bJr\.?\b/iu' => 'Jirón',
            '/\bCal\.?\b/iu' => 'Calle',
            '/\bPje\.?\b/iu' => 'Pasaje',
            '/\bUrb\.?\b/iu' => 'Urbanización',
            '/\bMz\.?\b/iu' => 'Manzana',
            '/\bLt\.?\b/iu' => 'Lote',
        ];
        foreach ($map as $re => $to) {
            $expanded = preg_replace($re, $to, $expanded) ?? $expanded;
        }
        $expanded = preg_replace('/(,\s*Lima){2,}/iu', ', Lima', $expanded) ?? $expanded;
        $expanded = preg_replace('/,?\s*Per[uú]\s*$/iu', '', $expanded) ?? $expanded;
        $variants = [$expanded.' Perú', $expanded];
        if (strcasecmp($expanded, $q) !== 0) {
            $variants[] = $q.' Perú';
            $variants[] = $q;
        }

        return array_values(array_unique(array_filter($variants)));
    }

    private function photonSearch(string $q, mixed $lat, mixed $lon): array
    {
        $params = [
            'q' => $q,
            'limit' => 8,
            'lang' => self::PHOTON_LANG,
            'bbox' => '-81.4,-18.4,-68.6,-0.05',
        ];
        if (is_numeric($lat) && is_numeric($lon)) {
            $params['lat'] = (float) $lat;
            $params['lon'] = (float) $lon;
        }
        $res = $this->http()->get('https://photon.komoot.io/api/', $params);
        if (! $res->successful()) {
            Log::info('[Geo] photon search HTTP '.$res->status());

            return [];
        }
        $out = [];
        foreach (($res->json('features') ?? []) as $f) {
            $g = $f['geometry']['coordinates'] ?? null;
            $p = $f['properties'] ?? [];
            if (! is_array($g) || count($g) < 2) {
                continue;
            }
            $country = mb_strtolower((string) ($p['country'] ?? ''));
            if ($country !== '' && ! str_contains($country, 'peru') && ! str_contains($country, 'perú')) {
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

    private function nominatimSearch(string $q, mixed $lat, mixed $lon): array
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
        if (is_numeric($lat) && is_numeric($lon)) {
            $la = (float) $lat;
            $lo = (float) $lon;
            $params['viewbox'] = ($lo - 0.12).','.($la + 0.12).','.($lo + 0.12).','.($la - 0.12);
            $params['bounded'] = 0;
        }
        $email = (string) config('services.geo.email', '');
        if ($email) {
            $params['email'] = $email;
        }
        $res = $this->http()->get($base.'/search', $params);
        if (! $res->successful()) {
            Log::info('[Geo] nominatim search HTTP '.$res->status());

            return [];
        }

        return is_array($res->json()) ? $res->json() : [];
    }

    private function mergeSearch(array $all, string $q): array
    {
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
        $num = (string) ($it['address']['house_number'] ?? '');
        if (in_array($cls, ['highway', 'building', 'place', 'amenity'], true)) {
            $score += 6;
        }
        if (in_array($type, ['house', 'building', 'residential', 'yes', 'road', 'living_street', 'primary', 'secondary', 'tertiary'], true)) {
            $score += 10;
        }
        if ($type === 'house' || $type === 'building') {
            $score += 12;
        }
        $qLow = mb_strtolower($q);
        foreach (preg_split('/[\s,]+/u', $qLow) as $tok) {
            if (mb_strlen($tok) < 3) {
                continue;
            }
            if (str_contains($display, $tok) || str_contains($road, $tok)) {
                $score += 5;
            }
        }
        if ($num !== '' && preg_match('/\b'.preg_quote($num, '/').'\b/u', $q)) {
            $score += 20;
        }
        if (str_contains($display, 'perú') || str_contains($display, 'peru')) {
            $score += 8;
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
                'lang' => self::PHOTON_LANG,
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
                'format' => 'jsonv2',
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
            $via = $a['road'] ?? $a['pedestrian'] ?? $a['residential'] ?? $a['footway']
                ?? $a['path'] ?? $a['neighbourhood'] ?? $a['suburb'] ?? '';
            $numero = $a['house_number'] ?? '';
            $dep = $a['state'] ?? $a['region'] ?? '';
            $prov = $a['province'] ?? $a['county'] ?? $a['state_district'] ?? $a['city'] ?? '';
            $dist = $a['city_district'] ?? $a['suburb'] ?? $a['town'] ?? $a['village']
                ?? $a['neighbourhood'] ?? $a['city'] ?? '';

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
