<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Fe\SunatService;
use Greenter\Model\Sale\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeController extends Controller
{
    /**
     * POST /api/fe/emitir
     * payload:
     *  - tipo: 'boleta' | 'factura'
     *  - cliente: { tipoDoc:'1|6', numDoc:'...', nombre:'...', direccion:'...' }
     *  - items: [{ codigo, descripcion, cantidad, precioUnit }]
     *  - moneda: 'PEN'
     */
    public function emitir(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|in:boleta,factura',
            'cliente.tipoDoc' => 'required|in:1,6',
            'cliente.numDoc'  => 'required|string',
            'cliente.nombre'  => 'required|string',
            'cliente.direccion' => 'nullable|string',
            'moneda' => 'nullable|in:PEN,USD',
            'items'  => 'required|array|min:1',
            'items.*.codigo' => 'nullable|string',
            'items.*.descripcion' => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:1',
            'items.*.precioUnit' => 'required|numeric|min:0',
        ]);

        try {
            $fe = app(SunatService::class);
            $invoice = $fe->buildInvoice($data);
            $res = $fe->sendAndStore($invoice);
            return response()->json($res, $res['success'] ? 200 : 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error emitiendo: '.$e->getMessage(),
            ], 500);
        }
    }

    // Descargas
    public function xml($name)
    {
        $path = "fe/xml/{$name}.xml";
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->file(storage_path("app/{$path}"), [
            'Content-Type' => 'application/xml'
        ]);
    }

    public function cdr($name)
    {
        $path = "fe/cdr/{$name}.zip";
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(storage_path("app/{$path}"));
    }

    public function pedidoFile($id, $kind)
    {
        try {
            return $this->pedidoFileUnsafe($id, $kind);
        } catch (\Throwable $e) {
            \Log::error('[fe.pedidoFile] '.$id.'/'.$kind.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
            return $this->feFileResponse(null, 500, 'No se pudo abrir el archivo.');
        }
    }

    private function pedidoFileUnsafe($id, $kind)
    {
        $kind = strtolower((string) $kind);
        if (!in_array($kind, ['pdf', 'xml', 'cdr'], true)) {
            return $this->feFileResponse(null, 404, 'Tipo inválido.');
        }
        $pedido = \App\Models\Pedido::find($id);
        if (!$pedido) {
            return $this->feFileResponse(null, 404, 'Pedido no encontrado.');
        }
        $rel = match ($kind) {
            'pdf' => $pedido->sunat_pdf,
            'xml' => $pedido->sunat_xml,
            'cdr' => $pedido->sunat_cdr,
            default => null,
        };
        if ($kind === 'pdf' && (!$rel || !Storage::disk('public')->exists($rel))) {
            try {
                $rel = app(\App\Services\ComprobanteService::class)->asegurarPdf($pedido);
            } catch (\Throwable $e) {
                \Log::warning('[fe.pedidoFile] pdf '.$id.': '.$e->getMessage());
                $rel = null;
            }
        }
        if ($kind === 'pdf' && $rel && !Storage::disk('public')->exists($rel)) {
            $rel = null;
        }
        if (!$rel || !Storage::disk('public')->exists($rel)) {
            return $this->feFileResponse(null, 404, 'Archivo no disponible.');
        }
        $mime = match ($kind) {
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            default => 'application/zip',
        };
        $name = basename($rel);

        return response(Storage::disk('public')->get($rel), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$name.'"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,HEAD,OPTIONS',
            'Access-Control-Allow-Headers' => '*',
        ]);
    }

    private function feFileResponse($content, int $status, string $message)
    {
        return response()->json(['message' => $message], $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS')
            ->header('Access-Control-Allow-Headers', '*');
    }

    public static function resolvePrefixed(string $dir, string $name): ?string
    {
        $disk = Storage::disk('public');
        $exact = trim($dir, '/').'/'.$name;
        $candidates = [];
        foreach ($disk->files($dir) as $f) {
            if (str_ends_with($f, '/'.$name) || $f === $name) {
                $candidates[] = $f;
            }
        }
        $prefixed = array_values(array_filter($candidates, function ($f) use ($name) {
            return (bool) preg_match('/\/\d+-'.preg_quote($name, '/').'$/', $f);
        }));
        if ($prefixed) {
            natsort($prefixed);
            return end($prefixed) ?: null;
        }
        if ($disk->exists($exact)) {
            return $exact;
        }

        return $candidates[0] ?? null;
    }
}
