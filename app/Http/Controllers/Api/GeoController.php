<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoController extends Controller
{
    public function search(Request $request)
    {
        try {
            $q = trim((string) $request->query('q', ''));
            if ($q === '' || ! $this->geoOn()) {
                return response()->json([]);
            }
            $q = substr($q, 0, 200);
            $biasLat = $request->query('lat');
            $biasLon = $request->query('lon');

            $merged = [];
            foreach ($this->queryVariants($q) as $variant) {
                $nomi = $this->nominatimSearch($variant, $biasLat, $biasLon);
                $photon = $this->photonSearch($variant, $biasLat, $biasLon);
                $merged = $this->mergeSearch(array_merge($nomi, $photon), $variant);
                if ($merged !== []) {
                    break;
                }
            }

            return response()->json($merged);
        } catch (\Throwable $e) {
            Log::warning('[Geo] search: '.$e->getMessage());

            return response()->json([]);
        }
    }

    public function reverse(Request $request)
    {
        try {
            if (! $this->geoOn()) {
                return response()->json([
                    'via' => '', 'numero' => '', 'departamento' => '',
                    'provincia' => '', 'distrito' => '', 'display' => null,
                ]);
            }

            $lat = (float) $request->query('lat');
            $lon = (float) $request->query('lon');
            if ($lat === 0.0 && $lon === 0.0) {
                return response()->json(['message' => 'lat/lon'], 400);
            }

            $out = $this->nominatimReverse($lat, $lon) ?: $this->photonReverse($lat, $lon);
            if (! $out) {
                $out = [
                    'via' => '',
                    'numero' => '',
                    'departamento' => '',
                    'provincia' => '',
                    'distrito' => '',
                    'display' => 'Ubicación '.$lat.', '.$lon,
                ];
            }

            return response()->json($out);
        } catch (\Throwable $e) {
            Log::warning('[Geo] reverse: '.$e->getMessage());

            return response()->json([
                'via' => '',
                'numero' => '',
                'departamento' => '',
                'provincia' => '',
                'distrito' => '',
                'display' => null,
            ]);
        }
    }

    private function geoOn(): bool
    {
        $v = config('services.geo.enabled', true);

        return ! in_array($v, [false, 0, '0', 'false', 'off', 'no'], true);
    }

    private function ua(): string
    {
        $email = (string) (config('services.geo.email') ?: 'contacto@estilodorado.net.pe');

        return 'EstiloDorado/1.3 (https://estilodorado.net.pe; '.$email.')';
    }

    private function client()
    {
        return Http::withHeaders([
            'User-Agent' => $this->ua(),
            'Accept-Language' => 'es',
            'Accept' => 'application/json',
        ])->timeout(8)->connectTimeout(5);
    }

    private function queryVariants(string $q): array
    {
        $q = trim(preg_replace('/\s+/', ' ', $q) ?: $q);
        $expanded = $q;
        foreach ([
            '/\bAvda\.?\s*/i' => 'Avenida ',
            '/\bAv\.?\s*/i' => 'Avenida ',
            '/\bJr\.?\s*/i' => 'Jiron ',
            '/\bCal\.?\s*/i' => 'Calle ',
            '/\bPje\.?\s*/i' => 'Pasaje ',
            '/\bUrb\.?\s*/i' => 'Urbanizacion ',
        ] as $re => $to) {
            $tmp = preg_replace($re, $to, $expanded);
            if (is_string($tmp)) {
                $expanded = $tmp;
            }
        }
        $expanded = preg_replace('/(,\s*Lima){2,}/i', ', Lima', $expanded) ?: $expanded;
        $expanded = trim($expanded);
        $out = [];
        foreach ([$expanded.' Peru', $expanded, $q] as $v) {
            $v = trim($v);
            if ($v !== '' && ! in_array($v, $out, true)) {
                $out[] = $v;
            }
        }

        return $out;
    }

    private function photonSearch(string $q, mixed $lat, mixed $lon): array
    {
        try {
            $params = [
                'q' => $q,
                'limit' => 8,
                'lang' => 'en',
            ];
            if (is_numeric($lat) && is_numeric($lon)) {
                $params['lat'] = (float) $lat;
                $params['lon'] = (float) $lon;
            }
            $res = $this->client()->get('https://photon.komoot.io/api/', $params);
            if (! $res->successful()) {
                return [];
            }
            $out = [];
            $features = $res->json('features');
            if (! is_array($features)) {
                return [];
            }
            foreach ($features as $f) {
                if (! is_array($f)) {
                    continue;
                }
                $g = $f['geometry']['coordinates'] ?? null;
                $p = $f['properties'] ?? [];
                if (! is_array($g) || count($g) < 2 || ! is_array($p)) {
                    continue;
                }
                $country = strtolower((string) ($p['country'] ?? ''));
                if ($country !== '' && ! str_contains($country, 'peru')) {
                    continue;
                }
                $out[] = [
                    'lat' => (string) $g[1],
                    'lon' => (string) $g[0],
                    'display_name' => $this->photonDisplay($p),
                    'class' => (string) ($p['osm_key'] ?? ''),
                    'type' => (string) ($p['osm_value'] ?? ''),
                    'address' => [
                        'road' => (string) ($p['street'] ?? $p['name'] ?? ''),
                        'house_number' => (string) ($p['housenumber'] ?? ''),
                        'city' => (string) ($p['city'] ?? ''),
                        'suburb' => (string) ($p['district'] ?? $p['locality'] ?? ''),
                        'state' => (string) ($p['state'] ?? ''),
                        'country' => (string) ($p['country'] ?? ''),
                    ],
                ];
            }

            return $out;
        } catch (\Throwable $e) {
            Log::info('[Geo] photon search: '.$e->getMessage());

            return [];
        }
    }

    private function nominatimSearch(string $q, mixed $lat, mixed $lon): array
    {
        try {
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
                $params['viewbox'] = ($lo - 0.18).','.($la + 0.18).','.($lo + 0.18).','.($la - 0.18);
            }
            $res = $this->client()->get($base.'/search', $params);
            if (! $res->successful()) {
                return [];
            }
            $json = $res->json();
            if (! is_array($json)) {
                return [];
            }
            if (isset($json['lat'], $json['lon'])) {
                $json = [$json];
            }
            $out = [];
            foreach ($json as $it) {
                if (is_array($it) && isset($it['lat'], $it['lon'])) {
                    $out[] = $it;
                }
            }

            return $out;
        } catch (\Throwable $e) {
            Log::info('[Geo] nominatim search: '.$e->getMessage());

            return [];
        }
    }

    private function mergeSearch(array $all, string $q): array
    {
        $hits = [];
        foreach ($all as $it) {
            if (is_array($it) && isset($it['lat'], $it['lon'])) {
                $hits[] = $it;
            }
        }
        usort($hits, function ($a, $b) use ($q) {
            return $this->scoreHit($b, $q) <=> $this->scoreHit($a, $q);
        });
        $seen = [];
        $out = [];
        foreach ($hits as $it) {
            $k = round((float) $it['lat'], 4).':'.round((float) $it['lon'], 4);
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
        $display = strtolower((string) ($it['display_name'] ?? ''));
        $addr = is_array($it['address'] ?? null) ? $it['address'] : [];
        $road = strtolower((string) ($addr['road'] ?? ''));
        $num = (string) ($addr['house_number'] ?? '');
        if (in_array($cls, ['highway', 'building', 'place', 'amenity'], true)) {
            $score += 6;
        }
        if (in_array($type, ['house', 'building', 'residential', 'yes', 'road', 'living_street', 'primary', 'secondary', 'tertiary'], true)) {
            $score += 10;
        }
        if ($type === 'house' || $type === 'building') {
            $score += 12;
        }
        $qLow = strtolower($q);
        foreach (preg_split('/[\s,]+/', $qLow) ?: [] as $tok) {
            if (strlen($tok) < 3) {
                continue;
            }
            if (str_contains($display, $tok) || str_contains($road, $tok)) {
                $score += 5;
            }
        }
        if ($num !== '' && str_contains($qLow, strtolower($num))) {
            $score += 20;
        }
        if (str_contains($display, 'peru') || str_contains($display, 'perú')) {
            $score += 8;
        }
        $score += (int) round(((float) ($it['importance'] ?? 0)) * 10);

        return $score;
    }

    private function photonReverse(float $lat, float $lon): ?array
    {
        try {
            $res = $this->client()->get('https://photon.komoot.io/reverse', [
                'lat' => $lat,
                'lon' => $lon,
                'lang' => 'en',
            ]);
            if (! $res->successful()) {
                return null;
            }
            $features = $res->json('features');
            $f = is_array($features) ? ($features[0] ?? null) : null;
            if (! is_array($f)) {
                return null;
            }
            $p = is_array($f['properties'] ?? null) ? $f['properties'] : [];

            return $this->normalizeReverse(
                (string) ($p['street'] ?? $p['name'] ?? ''),
                (string) ($p['housenumber'] ?? ''),
                (string) ($p['state'] ?? ''),
                (string) ($p['city'] ?? $p['county'] ?? ''),
                (string) ($p['district'] ?? $p['locality'] ?? $p['city'] ?? ''),
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
            $res = $this->client()->get($base.'/reverse', [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lon,
                'addressdetails' => 1,
                'zoom' => 18,
                'accept-language' => 'es',
            ]);
            if (! $res->successful()) {
                return null;
            }
            $data = $res->json();
            if (! is_array($data)) {
                return null;
            }
            $a = is_array($data['address'] ?? null) ? $data['address'] : [];
            $via = (string) ($a['road'] ?? $a['pedestrian'] ?? $a['residential'] ?? $a['footway']
                ?? $a['path'] ?? $a['neighbourhood'] ?? $a['suburb'] ?? '');
            $numero = (string) ($a['house_number'] ?? '');
            $dep = (string) ($a['state'] ?? $a['region'] ?? '');
            $prov = (string) ($a['province'] ?? $a['county'] ?? $a['state_district'] ?? $a['city'] ?? '');
            $dist = (string) ($a['city_district'] ?? $a['suburb'] ?? $a['town'] ?? $a['village']
                ?? $a['neighbourhood'] ?? $a['city'] ?? '');

            return $this->normalizeReverse($via, $numero, $dep, $prov, $dist, (string) ($data['display_name'] ?? ''));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function photonDisplay(array $p): string
    {
        $parts = array_filter([
            trim((string) ($p['street'] ?? $p['name'] ?? '').' '.(string) ($p['housenumber'] ?? '')),
            $p['district'] ?? null,
            $p['city'] ?? null,
            $p['state'] ?? null,
            $p['country'] ?? null,
        ], fn ($v) => is_string($v) && trim($v) !== '');

        return implode(', ', $parts);
    }

    private function normalizeReverse(string $via, string $numero, string $dep, string $prov, string $dist, string $display): array
    {
        $via = trim($via);
        $numero = trim($numero);
        if ($via === '' && $display !== '') {
            $head = trim(explode(',', $display)[0] ?? '');
            if ($head !== '') {
                if (preg_match('/^(.*?)[\s,]+(\d+[A-Za-z\-]*)\s*$/', $head, $m)) {
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
            'display' => $display !== '' ? $display : null,
        ];
    }
}
