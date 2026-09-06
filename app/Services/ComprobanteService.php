<?php

namespace App\Services;

use App\Models\Pedido;
use DateTime;
use DateTimeZone;
use Dompdf\Dompdf;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

// QR (chillerlan)
use chillerlan\QRCode\QRCode as ChilliQRCode;
use chillerlan\QRCode\QROptions;

class ComprobanteService
{
    private function buildSee(): See
    {
        $see = new See();

        $pemPath = base_path(env('SUNAT_CERT_PEM', 'storage/certs/sunat.pem'));
        $see->setCertificate(file_get_contents($pemPath));

        $see->setService(env('SUNAT_BETA', true)
            ? SunatEndpoints::FE_BETA
            : SunatEndpoints::FE_PRODUCCION);

        $see->setClaveSOL(
            (string)env('SUNAT_RUC'),
            (string)env('SUNAT_SOL_USER'),
            (string)env('SUNAT_SOL_PASS'),
        );

        return $see;
    }

    private function datosEmisor(): array
    {
        $e = config('services.empresa', []);
        $dir = trim((string) ($e['direccion'] ?? 'Prolongación Yauli Nro. S/N'));
        $dist = trim((string) ($e['dist'] ?? 'Chaupimarca'));
        $prov = trim((string) ($e['prov'] ?? 'Pasco'));
        $depa = trim((string) ($e['depa'] ?? 'Pasco'));
        $linea = $dir;
        if ($dist !== '' || $prov !== '' || $depa !== '') {
            $linea .= "\n".trim($dist.', '.$prov.', '.$depa, ' ,');
        }

        return [
            'ruc' => (string) ($e['ruc'] ?? '20614857430'),
            'ruc_visual' => (string) ($e['ruc'] ?? '20614857430'),
            'razon' => (string) ($e['razon'] ?? 'ESTILO DORADO S.A.C.'),
            'comercial' => (string) ($e['comercial'] ?? 'Estilo Dorado'),
            'direccion' => $linea,
            'ubigeo' => (string) ($e['ubigeo'] ?? '190101'),
            'depa' => $depa,
            'prov' => $prov,
            'dist' => $dist,
            'dir_corta' => $dir,
        ];
    }

    private function buildCompany(): Company
    {
        $e = $this->datosEmisor();
        $addr = (new Address())
            ->setUbigueo($e['ubigeo'])
            ->setDepartamento($e['depa'])
            ->setProvincia($e['prov'])
            ->setDistrito($e['dist'])
            ->setUrbanizacion('-')
            ->setDireccion($e['dir_corta'])
            ->setCodLocal('0000');

        return (new Company())
            ->setRuc($e['ruc'])
            ->setRazonSocial($e['razon'])
            ->setNombreComercial($e['comercial'])
            ->setAddress($addr);
    }

    public function emitir(Pedido $pedido, array $payload): array
    {
        $igv     = (float)env('SUNAT_IGV', 0.18);
        $serie   = $pedido->comprobante_serie;     // F001/B001
        $numero  = (int)$pedido->comprobante_numero;
        $tipo    = $pedido->comprobante_tipo;      // 'FA' | 'BO'
        $tipoDocCpe = $tipo === 'FA' ? '01' : '03';

        $pedido->loadMissing(['cliente', 'detalles.producto']);
        $this->guardarMeta($pedido, $payload);
        $cuenta = $pedido->cliente;
        $nombreCuenta = trim(trim((string) ($cuenta?->nombre ?? '')).' '.trim((string) ($cuenta?->apellido ?? '')));
        $dirCuenta = trim((string) ($pedido->direccion_entrega ?: ($cuenta?->direccion ?? '')));

        if ($tipo === 'FA') {
            $cliTipoDoc  = '6';
            $cliNumDoc   = preg_replace('/\D+/', '', (string) ($payload['factura']['ruc'] ?? '')) ?: '00000000000';
            $cliNombre   = trim((string) ($payload['factura']['razonSocial'] ?? '')) ?: ($nombreCuenta ?: 'CLIENTE');
            $cliDir      = trim((string) ($payload['factura']['direccion'] ?? '')) ?: ($dirCuenta !== '' ? $dirCuenta : '-');
        } else {
            $cliTipoDoc  = '1';
            $cliNumDoc   = preg_replace('/\D+/', '', (string) ($payload['boleta']['dni'] ?? '')) ?: '00000000';
            $cliNombre   = trim((string) ($payload['boleta']['nombres'] ?? '')) ?: ($nombreCuenta ?: 'CLIENTE');
            $cliDir      = trim((string) ($payload['boleta']['direccion'] ?? '')) ?: ($dirCuenta !== '' ? $dirCuenta : '-');
        }

        $client = (new Client())
            ->setTipoDoc($cliTipoDoc)
            ->setNumDoc($cliNumDoc)
            ->setRznSocial($cliNombre);

        // -------- Empresa (emisor) --------
        $company = $this->buildCompany();

        // -------- Detalles y totales --------
        $mtoGravada = 0.0;
        $mtoIGV     = 0.0;
        $mtoTotal   = 0.0;
        $detalles   = [];

        foreach ($pedido->detalles()->with('producto')->get() as $d) {
            $desc = $d->producto?->nombre ?? ('Producto #'.$d->id_producto);
            $cant = (float)$d->cantidad;
            $pUnitConIGV = (float)$d->precio_unitario;        // precios incluyen IGV
            $pUnitSinIGV = round($pUnitConIGV / (1 + $igv), 6);
            $base        = round($pUnitSinIGV * $cant, 2);
            $igvDet      = round($base * $igv, 2);
            $totalDet    = round($pUnitConIGV * $cant, 2);

            $item = (new SaleDetail())
                ->setCodProducto((string)$d->id_producto)
                ->setUnidad('NIU')
                ->setCantidad($cant)
                ->setMtoValorUnitario($pUnitSinIGV)
                ->setDescripcion($desc)
                ->setMtoBaseIgv($base)
                ->setPorcentajeIgv($igv * 100)
                ->setIgv($igvDet)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igvDet)
                ->setMtoValorVenta($base)
                ->setMtoPrecioUnitario($pUnitConIGV);

            $detalles[]  = $item;
            $mtoGravada += $base;
            $mtoIGV     += $igvDet;
            $mtoTotal   += $totalDet;
        }

        $envioCosto = 0.0;
        $envioEtiqueta = 'Envío';
        if (isset($pedido->costo_envio) && (float) $pedido->costo_envio > 0) {
            $envioCosto = (float) $pedido->costo_envio;
            $envioEtiqueta = $pedido->envio_etiqueta ?: 'Envío';
        } elseif (preg_match('/ENVIO:([0-9.]+)\|([^\n]+)/', (string) ($pedido->observacion ?? ''), $m)) {
            $envioCosto = (float) $m[1];
            $envioEtiqueta = trim($m[2]) ?: 'Envío';
        }
        if ($envioCosto > 0) {
            $pUnitConIGV = round($envioCosto, 2);
            $pUnitSinIGV = round($pUnitConIGV / (1 + $igv), 6);
            $base        = round($pUnitSinIGV, 2);
            $igvDet      = round($base * $igv, 2);
            $detalles[] = (new SaleDetail())
                ->setCodProducto('ENVIO')
                ->setUnidad('ZZ')
                ->setCantidad(1)
                ->setMtoValorUnitario($pUnitSinIGV)
                ->setDescripcion($envioEtiqueta)
                ->setMtoBaseIgv($base)
                ->setPorcentajeIgv($igv * 100)
                ->setIgv($igvDet)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($igvDet)
                ->setMtoValorVenta($base)
                ->setMtoPrecioUnitario($pUnitConIGV);
            $mtoGravada += $base;
            $mtoIGV     += $igvDet;
            $mtoTotal   += $pUnitConIGV;
        }

        // -------- Comprobante --------
        $tz = new DateTimeZone('America/Lima');
        $emision = $pedido->fecha_pedido
            ? Carbon::parse($pedido->fecha_pedido)->timezone('America/Lima')
            : Carbon::now('America/Lima');

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')
            ->setTipoDoc($tipoDocCpe)
            ->setSerie($serie)
            ->setCorrelativo((string)$numero)
            ->setFechaEmision(new DateTime($emision->format('Y-m-d H:i:s'), $tz))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda('PEN')
            ->setCompany($company)
            ->setClient($client)
            ->setMtoOperGravadas(round($mtoGravada, 2))
            ->setMtoIGV(round($mtoIGV, 2))
            ->setTotalImpuestos(round($mtoIGV, 2))
            ->setValorVenta(round($mtoGravada, 2))
            ->setSubTotal(round($mtoTotal, 2))
            ->setMtoImpVenta(round($mtoTotal, 2))
            ->setDetails($detalles);

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue('SON ' . $this->montoEnLetras($mtoTotal) . ' SOLES');
        $invoice->setLegends([$legend]);

        $invoiceName = $invoice->getName();

        // -------- Envío a SUNAT --------
        $see    = $this->buildSee();
        $result = $see->send($invoice);

        if (!$result->isSuccess()) {
            $err = $result->getError();
            Log::error('[SUNAT ERROR]', [
                'code' => $err?->getCode(),
                'message' => $err?->getMessage() ?: 'Sin detalle',
            ]);
        }

        // -------- Guardado de archivos --------
        $tipoCarpeta = $tipo; // 'FA' o 'BO'
        $num8     = str_pad($numero, 8, '0', STR_PAD_LEFT);
        $friendly = "{$serie}-{$num8}";

        // XML firmado
        $lastXml = '';
        if (method_exists($see, 'getFactory') && $see->getFactory()) {
            $lastXml = $see->getFactory()->getLastXml();
        } elseif (method_exists($see, 'getLastXml')) {
            $lastXml = $see->getLastXml();
        }

        $xmlRel = "comprobantes/xml/{$tipoCarpeta}/{$serie}/{$pedido->id_pedido}-{$friendly}.xml";
        Storage::disk('public')->put($xmlRel, $lastXml ?: '<xml/>');

        // CDR
        $cdrRel = null;
        if ($result->getCdrZip()) {
            $cdrRel = "comprobantes/cdr/{$tipoCarpeta}/{$pedido->id_pedido}-R-{$friendly}.zip";
            Storage::disk('public')->put($cdrRel, $result->getCdrZip());
        }

        // -------- HASH (DigestValue) del XML --------
        $hash = '-';
        if ($lastXml && preg_match('/<ds:DigestValue>([^<]+)<\/ds:DigestValue>/i', $lastXml, $m)) {
            $hash = $m[1];
        }

        // -------- QR (en base64, sin imagick) --------
        $qrText = implode('|', [
            env('SUNAT_RUC'),
            $tipoDocCpe,           // 01/03
            $serie,
            $num8,
            number_format($mtoIGV, 2, '.', ''),
            number_format($mtoTotal, 2, '.', ''),
            date('Y-m-d'),
            $cliTipoDoc,
            $cliNumDoc,
        ]);

        $qrOptions = new QROptions([
            'outputType' => ChilliQRCode::OUTPUT_IMAGE_PNG,
            'scale'      => 3, // tamaño
            'eccLevel'   => ChilliQRCode::ECC_M,
        ]);
        $qrRaw = (new ChilliQRCode($qrOptions))->render($qrText);
        $qrB64 = is_string($qrRaw) && str_starts_with($qrRaw, 'data:')
            ? $qrRaw
            : 'data:image/png;base64,'.base64_encode((string) $qrRaw);

        $logoB64 = null;
        foreach (['brand/logo_edorado.jpeg', 'Brand/logo_edorado.jpeg', 'brand/logo_edorado.jpg'] as $relLogo) {
            $logoPath = public_path($relLogo);
            if (is_file($logoPath)) {
                $logoB64 = 'data:image/jpeg;base64,'.base64_encode((string) file_get_contents($logoPath));
                break;
            }
        }

        $pdfRel = "comprobantes/pdf/{$tipoCarpeta}/{$serie}/{$pedido->id_pedido}-{$friendly}.pdf";
        $pdfItems = [];
        foreach ($pedido->detalles as $d) {
            $pdfItems[] = (object) [
                'id_producto' => $d->id_producto,
                'producto' => (object) ['nombre' => $d->producto?->nombre ?? ('Producto #'.$d->id_producto)],
                'cantidad' => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
            ];
        }
        if ($envioCosto > 0) {
            $pdfItems[] = (object) [
                'id_producto' => 'ENVIO',
                'producto' => (object) ['nombre' => $envioEtiqueta],
                'cantidad' => 1,
                'precio_unitario' => $envioCosto,
            ];
        }
        $html = View::make('fe.comprobante', [
            'tipo'         => $tipo,                   // 'FA'/'BO'
            'serie'        => $serie,
            'numero'       => $num8,
            'emisor'       => $this->datosEmisor(),
            'cliente'      => [
                'doc_label'  => $tipo === 'FA' ? 'RUC' : 'DNI',
                'doc'        => $cliNumDoc,
                'nombre'     => $cliNombre,
                'direccion'  => $cliDir ?: '-',
            ],
            'moneda'       => 'PEN (PEN)',
            'mto_gravada'  => number_format($mtoGravada, 2, '.', ''),
            'mto_igv'      => number_format($mtoIGV, 2, '.', ''),
            'mto_total'    => number_format($mtoTotal, 2, '.', ''),
            'legend'       => 'SON: '.$this->montoEnLetras($mtoTotal).' SOLES',
            'items'        => $pdfItems,
            'hash'         => $hash,
            'qrB64'        => $qrB64,
            'logoB64'      => $logoB64,
            'emitido'      => date('Y-m-d H:i:s'),
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();
        Storage::disk('public')->put($pdfRel, $dompdf->output());

        return [
            'invoiceName' => $invoiceName,
            'xml'    => $xmlRel,
            'cdr'    => $cdrRel,
            'pdf'    => $pdfRel,
            'serie'  => $serie,
            'numero' => $numero,
        ];
    }

    /** Emite SUNAT si se puede; si falla, igual deja PDF de boleta/factura. */
    public function emitirOPdf(Pedido $pedido, array $payload): void
    {
        $this->guardarMeta($pedido, $payload);
        try {
            $res = $this->emitir($pedido, $payload);
            $pedido->sunat_pdf = $res['pdf'] ?? $pedido->sunat_pdf;
            $pedido->sunat_xml = $res['xml'] ?? $pedido->sunat_xml;
            $pedido->sunat_cdr = $res['cdr'] ?? $pedido->sunat_cdr;
            $pedido->save();
        } catch (\Throwable $e) {
            Log::warning('[CPE] emitir falló pedido '.$pedido->id_pedido.': '.$e->getMessage());
        }
        if (!$pedido->sunat_pdf || !Storage::disk('public')->exists($pedido->sunat_pdf)) {
            $pdf = $this->asegurarPdf($pedido);
            if ($pdf) {
                $pedido->sunat_pdf = $pdf;
                $pedido->save();
            }
        }
    }

    public function guardarMeta(Pedido $pedido, array $payload): void
    {
        $tipo = strtoupper((string) ($pedido->comprobante_tipo ?: ($payload['comprobante'] ?? 'BO')));
        $cli = [
            'doc_label' => $tipo === 'FA' ? 'RUC' : 'DNI',
            'doc' => '',
            'nombre' => '',
            'direccion' => (string) ($pedido->direccion_entrega ?: ''),
        ];
        if ($tipo === 'FA' && isset($payload['factura']) && is_array($payload['factura'])) {
            $f = $payload['factura'];
            $cli['doc'] = preg_replace('/\D+/', '', (string) ($f['ruc'] ?? '')) ?: '';
            $cli['nombre'] = trim((string) ($f['razonSocial'] ?? ''));
            $cli['direccion'] = trim((string) ($f['direccion'] ?? '')) ?: $cli['direccion'];
        } elseif (isset($payload['boleta']) && is_array($payload['boleta'])) {
            $b = $payload['boleta'];
            $cli['doc'] = preg_replace('/\D+/', '', (string) ($b['dni'] ?? '')) ?: '';
            $cli['nombre'] = trim((string) ($b['nombres'] ?? ''));
            $cli['direccion'] = trim((string) ($b['direccion'] ?? '')) ?: $cli['direccion'];
        }
        Storage::disk('public')->put(
            'comprobantes/meta/'.$pedido->id_pedido.'.json',
            json_encode(['tipo' => $tipo, 'cliente' => $cli], JSON_UNESCAPED_UNICODE)
        );
    }

    /** @return array{tipo?:string,cliente?:array{doc_label?:string,doc?:string,nombre?:string,direccion?:string}} */
    private function leerMeta(Pedido $pedido): array
    {
        $rel = 'comprobantes/meta/'.$pedido->id_pedido.'.json';
        if (!Storage::disk('public')->exists($rel)) {
            return [];
        }
        $j = json_decode((string) Storage::disk('public')->get($rel), true);

        return is_array($j) ? $j : [];
    }

    /** Genera el PDF del pedido si falta (sin reenviar a SUNAT). */
    public function asegurarPdf(Pedido $pedido): ?string
    {
        try {
            return $this->asegurarPdfUnsafe($pedido);
        } catch (\Throwable $e) {
            Log::error('[asegurarPdf] pedido '.$pedido->id_pedido.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
            return null;
        }
    }

    private function asegurarPdfUnsafe(Pedido $pedido): ?string
    {
        $pedido->loadMissing(['cliente', 'detalles.producto']);
        if ($pedido->sunat_pdf && Storage::disk('public')->exists($pedido->sunat_pdf)) {
            return $pedido->sunat_pdf;
        }

        $igv = (float) env('SUNAT_IGV', 0.18);
        $tipo = strtoupper((string) ($pedido->comprobante_tipo ?: 'BO'));
        if (!in_array($tipo, ['FA', 'BO'], true)) {
            $tipo = 'BO';
        }
        $serie = $pedido->comprobante_serie ?: ($tipo === 'FA' ? 'F001' : 'B001');
        $numero = (int) $pedido->comprobante_numero;
        if ($numero <= 0) {
            $numero = (int) $pedido->id_pedido;
        }
        $num8 = str_pad((string) $numero, 8, '0', STR_PAD_LEFT);
        $friendly = "{$serie}-{$num8}";

        $cuenta = $pedido->cliente;
        $meta = $this->leerMeta($pedido);
        $metaCli = is_array($meta['cliente'] ?? null) ? $meta['cliente'] : [];
        $cliNombre = trim((string) ($metaCli['nombre'] ?? ''))
            ?: (trim(trim((string) ($cuenta?->nombre ?? '')).' '.trim((string) ($cuenta?->apellido ?? ''))) ?: 'CLIENTE');
        $cliDir = trim((string) ($metaCli['direccion'] ?? ''))
            ?: (trim((string) ($pedido->direccion_entrega ?: ($cuenta?->direccion ?? ''))) ?: '-');
        $cliNumDoc = preg_replace('/\D+/', '', (string) ($metaCli['doc'] ?? ''))
            ?: ($tipo === 'FA' ? '00000000000' : '00000000');

        $envioCosto = 0.0;
        $envioEtiqueta = 'Envío';
        if (isset($pedido->costo_envio) && (float) $pedido->costo_envio > 0) {
            $envioCosto = (float) $pedido->costo_envio;
            $envioEtiqueta = $pedido->envio_etiqueta ?: 'Envío';
        }

        $mtoTotal = 0.0;
        $pdfItems = [];
        foreach ($pedido->detalles as $d) {
            $pdfItems[] = (object) [
                'id_producto' => $d->id_producto,
                'producto' => (object) ['nombre' => $d->producto?->nombre ?? ('Producto #'.$d->id_producto)],
                'cantidad' => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
            ];
            $mtoTotal += (float) $d->cantidad * (float) $d->precio_unitario;
        }
        if ($envioCosto > 0) {
            $pdfItems[] = (object) [
                'id_producto' => 'ENVIO',
                'producto' => (object) ['nombre' => $envioEtiqueta],
                'cantidad' => 1,
                'precio_unitario' => $envioCosto,
            ];
            $mtoTotal += $envioCosto;
        }
        if ($mtoTotal <= 0 && (float) $pedido->total > 0) {
            $mtoTotal = (float) $pedido->total;
        }
        $mtoGravada = round($mtoTotal / (1 + $igv), 2);
        $mtoIGV = round($mtoTotal - $mtoGravada, 2);

        $logoB64 = null;
        foreach (['brand/logo_edorado.jpeg', 'Brand/logo_edorado.jpeg'] as $relLogo) {
            $logoPath = public_path($relLogo);
            if (is_file($logoPath)) {
                $logoB64 = 'data:image/jpeg;base64,'.base64_encode((string) file_get_contents($logoPath));
                break;
            }
        }

        $emitido = now('America/Lima')->format('Y-m-d H:i:s');
        try {
            if ($pedido->fecha_pedido) {
                $emitido = Carbon::parse($pedido->fecha_pedido)->timezone('America/Lima')->format('Y-m-d H:i:s');
            }
        } catch (\Throwable $e) {
        }

        $html = View::make('fe.comprobante', [
            'tipo' => $tipo,
            'serie' => $serie,
            'numero' => $num8,
            'emisor' => $this->datosEmisor(),
            'cliente' => [
                'doc_label' => $tipo === 'FA' ? 'RUC' : 'DNI',
                'doc' => $cliNumDoc,
                'nombre' => $cliNombre,
                'direccion' => $cliDir,
            ],
            'moneda' => 'PEN (PEN)',
            'mto_gravada' => number_format($mtoGravada, 2, '.', ''),
            'mto_igv' => number_format($mtoIGV, 2, '.', ''),
            'mto_total' => number_format($mtoTotal, 2, '.', ''),
            'legend' => 'SON: '.$this->montoEnLetras($mtoTotal).' SOLES',
            'items' => $pdfItems,
            'hash' => '-',
            'qrB64' => null,
            'logoB64' => $logoB64,
            'emitido' => $emitido,
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $pdfRel = "comprobantes/pdf/{$tipo}/{$serie}/{$pedido->id_pedido}-{$friendly}.pdf";
        Storage::disk('public')->put($pdfRel, $dompdf->output());
        $pedido->sunat_pdf = $pdfRel;
        $pedido->save();

        return $pdfRel;
    }

    private function montoEnLetras(float $monto): string
    {
        $enteros = (int)$monto;
        $dec = (int)round(($monto - $enteros) * 100);

        $basicos = [
            0=>'CERO','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ',
            11=>'ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE',
            20=>'VEINTE',30=>'TREINTA',40=>'CUARENTA',50=>'CINCUENTA',
            60=>'SESENTA',70=>'SETENTA',80=>'OCHENTA',90=>'NOVENTA',
            100=>'CIEN',200=>'DOSCIENTOS',300=>'TRESCIENTOS',400=>'CUATROCIENTOS',
            500=>'QUINIENTOS',600=>'SEISCIENTOS',700=>'SETECIENTOS',800=>'OCHOCIENTOS',900=>'NOVECIENTOS'
        ];
        $toText = function($n) use (&$toText,$basicos): string {
            if ($n < 21) return $basicos[$n] ?? (string)$n;
            if ($n < 30) return 'VEINTI'.str_replace('VEINTE','', $toText($n-20));
            if ($n < 100) return ($basicos[intval($n/10)*10] ?? '').($n%10? ' Y '.($basicos[$n%10] ?? $n%10):'');
            if ($n == 100) return 'CIEN';
            if ($n < 1000) return ($basicos[intval($n/100)*100] ?? '').($n%100? ' '.$toText($n%100):'');
            if ($n < 1000000) {
                $m = intval($n/1000); $r = $n%1000;
                $pref = ($m==1)?'MIL':$toText($m).' MIL';
                return $pref.($r? ' '.$toText($r):'');
            }
            $m = intval($n/1000000); $r = $n%1000000;
            $pref = ($m==1)?'UN MILLÓN':$toText($m).' MILLONES';
            return $pref.($r? ' '.$toText($r):'');
        };
        return $toText($enteros).' CON '.str_pad((string)$dec,2,'0',STR_PAD_LEFT).'/100';
    }
}
