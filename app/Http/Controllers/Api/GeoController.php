<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeoController extends Controller
{
    public function search(Request $request)
    {
        try {
            $q = trim((string) $request->query('q', ''));
            if ($q === '') {
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
            $lat = (float) $request->query('lat');
            $lon = (float) $request->query('lon');
            if ($lat === 0.0 && $lon === 0.0) {
                return response()->json([
                    'via' => '', 'numero' => '', 'departamento' => '',
                    'provincia' => '', 'distrito' => '', 'display' => null,
                ]);
            }

            $out = $this->nominatimReverse($lat, $lon);
            if (! $out) {
                $out = $this->photonReverse($lat, $lon);
            }

            return response()->json($out ?: [
                'via' => '',
                'numero' => '',
                'departamento' => '',
                'provincia' => '',
                'distrito' => '',
                'display' => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[Geo] reverse: '.$e->getMessage());

            return response()->json([
                'via' => '', 'numero' => '', 'departamento' => '',
                'provincia' => '', 'distrito' => '', 'display' => null,
            ]);
        }
    }

    private function ua(): string
    {
        return 'EstiloDorado/1.4 (https://estilodorado.net.pe; contacto@estilodorado.net.pe)';
    }

    /** GET JSON con cURL (no Guzzle) para no depender de certificados de PHP. */
    private function getJson(string $url): ?array
    {
        if (! function_exists('curl_init')) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 8,
                    'header' => "User-Agent: ".$this->ua()."\r\nAccept: application/json\r\nAccept-Language: es\r\n",
                ],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $body = @file_get_contents($url, false, $ctx);
            if (! is_string($body) || $body === '') {
                return null;
            }
            $json = json_decode($body, true);

            return is_array($json) ? $json : null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: es',
                'User-Agent: '.$this->ua(),
            ],
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if (! is_string($body) || $body === '' || $code < 200 || $code >= 300) {
            if ($err) {
                Log::info('[Geo] curl '.$code.' '.$err);
            }

            return null;
        }
        $json = json_decode($body, true);

        return is_array($json) ? $json : null;
    }

    private function queryVariants(string $q): array
    {
        $q = trim(preg_replace('/\s+/', ' ', $q) ?: $q);
        $expanded = $q;
        $map = [
            '/\bAvda\.?\s*/i' => 'Avenida ',
            '/\bAv\.?\s*/i' => 'Avenida ',
            '/\bJr\.?\s*/i' => 'Jiron ',
            '/\bCal\.?\s*/i' => 'Calle ',
            '/\bPje\.?\s*/i' => 'Pasaje ',
            '/\bUrb\.?\s*/i' => 'Urbanizacion ',
        ];
        foreach ($map as $re => $to) {
            $tmp = preg_replace($re, $to, $expanded);
            if (is_string($tmp)) {
                $expanded = $tmp;
            }
        }
        $expanded = preg_replace('/(,\s*Lima){2,}/i', ', Lima', $expanded) ?: $expanded;
        $out = [];
        foreach ([trim($expanded).' Peru', trim($expanded), $q] as $v) {
            $v = trim($v);
            if ($v !== '' && ! in_array($v, $out, true)) {
                $out[] = $v;
            }
        }

        return $out;
    }

    private function photonSearch(string $q, $lat, $lon): array
    {
        try {
            $url = 'https://photon.komoot.io/api/?q='.rawurlencode($q).'&limit=8&lang=en';
            if (is_numeric($lat) && is_numeric($lon)) {
                $url .= '&lat='.rawurlencode((string) $lat).'&lon='.rawurlencode((string) $lon);
            }
            $json = $this->getJson($url);
            $features = is_array($json['features'] ?? null) ? $json['features'] : [];
            $out = [];
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
            return [];
        }
    }

    private function nominatimSearch(string $q, $lat, $lon): array
    {
        try {
            $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&addressdetails=1'
                .'&countrycodes=pe&accept-language=es&q='.rawurlencode($q);
            if (is_numeric($lat) && is_numeric($lon)) {
                $la = (float) $lat;
                $lo = (float) $lon;
                $url .= '&viewbox='.rawurlencode(($lo - 0.18).','.($la + 0.18).','.($lo + 0.18).','.($la - 0.18));
            }
            $json = $this->getJson($url);
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
        $display = strtolower((string) ($it['display_name'] ?? ''));
        $addr = is_array($it['address'] ?? null) ? $it['address'] : [];
        $road = strtolower((string) ($addr['road'] ?? ''));
        $qLow = strtolower($q);
        foreach (preg_split('/[\s,]+/', $qLow) ?: [] as $tok) {
            if (strlen($tok) < 3) {
                continue;
            }
            if (str_contains($display, $tok) || str_contains($road, $tok)) {
                $score += 5;
            }
        }
        $num = (string) ($addr['house_number'] ?? '');
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
        $json = $this->getJson(
            'https://photon.komoot.io/reverse?lat='.rawurlencode((string) $lat)
            .'&lon='.rawurlencode((string) $lon).'&lang=en'
        );
        $features = is_array($json['features'] ?? null) ? $json['features'] : [];
        $f = is_array($features[0] ?? null) ? $features[0] : null;
        if (! $f) {
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
    }

    private function nominatimReverse(float $lat, float $lon): ?array
    {
        $json = $this->getJson(
            'https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&zoom=18'
            .'&accept-language=es&lat='.rawurlencode((string) $lat).'&lon='.rawurlencode((string) $lon)
        );
        if (! is_array($json) || isset($json['error'])) {
            return null;
        }
        $a = is_array($json['address'] ?? null) ? $json['address'] : [];
        $via = (string) ($a['road'] ?? $a['pedestrian'] ?? $a['residential'] ?? $a['footway']
            ?? $a['path'] ?? $a['neighbourhood'] ?? $a['suburb'] ?? ($json['name'] ?? ''));
        $numero = (string) ($a['house_number'] ?? '');
        $dep = (string) ($a['state'] ?? $a['region'] ?? '');
        $prov = (string) ($a['province'] ?? $a['county'] ?? $a['state_district'] ?? $a['city'] ?? '');
        $dist = (string) ($a['city_district'] ?? $a['suburb'] ?? $a['town'] ?? $a['village']
            ?? $a['neighbourhood'] ?? $a['city'] ?? '');

        return $this->normalizeReverse($via, $numero, $dep, $prov, $dist, (string) ($json['display_name'] ?? ''));
    }

    private function photonDisplay(array $p): string
    {
        $street = trim((string) ($p['street'] ?? $p['name'] ?? '').' '.(string) ($p['housenumber'] ?? ''));
        $parts = [];
        foreach ([$street, $p['district'] ?? '', $p['locality'] ?? '', $p['city'] ?? '', $p['state'] ?? '', $p['country'] ?? ''] as $v) {
            $v = is_string($v) ? trim($v) : '';
            if ($v !== '' && ! in_array($v, $parts, true)) {
                $parts[] = $v;
            }
        }

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
