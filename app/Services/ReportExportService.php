<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Exporta reportes a CSV, XLSX (OOXML nativo) y PDF (Dompdf).
 * Sin dependencias extra más allá de dompdf (ya en composer) y ext-zip.
 */
class ReportExportService
{
    /**
     * @param  string  $basename  sin extensión, ej. reporte_clientes
     * @param  string  $title     título del documento
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  string  $ext  csv|xlsx|pdf
     */
    public function download(string $basename, string $title, array $headers, array $rows, string $ext)
    {
        $ext = strtolower($ext);

        return match ($ext) {
            'csv' => $this->csv($basename, $headers, $rows),
            'xlsx' => $this->xlsx($basename, $headers, $rows),
            'pdf' => $this->pdf($basename, $title, $headers, $rows),
            default => response()->json(['error' => ['message' => 'Formato no soportado']], 400),
        };
    }

    private function csv(string $basename, array $headers, array $rows): StreamedResponse
    {
        $filename = "{$basename}.csv";

        return new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($v) => $this->scalar($v), $row));
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function xlsx(string $basename, array $headers, array $rows)
    {
        if (! class_exists(ZipArchive::class)) {
            return response()->json([
                'error' => ['message' => 'ext-zip no disponible en el servidor para generar XLSX'],
            ], 500);
        }

        $binary = $this->buildXlsxBinary($headers, $rows);
        $filename = "{$basename}.xlsx";

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function pdf(string $basename, string $title, array $headers, array $rows)
    {
        if (! class_exists(Dompdf::class)) {
            return response()->json([
                'error' => ['message' => 'Dompdf no está instalado. Ejecuta: composer install'],
            ], 500);
        }

        $html = $this->pdfHtml($title, $headers, $rows);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $binary = $dompdf->output();
        $filename = "{$basename}.pdf";

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function pdfHtml(string $title, array $headers, array $rows): string
    {
        $generated = date('d/m/Y H:i');
        $th = '';
        foreach ($headers as $h) {
            $th .= '<th>'.e((string) $h).'</th>';
        }
        $body = '';
        foreach ($rows as $row) {
            $body .= '<tr>';
            foreach ($row as $cell) {
                $body .= '<td>'.e($this->scalar($cell)).'</td>';
            }
            $body .= '</tr>';
        }
        if ($body === '') {
            $body = '<tr><td colspan="'.count($headers).'" style="text-align:center;color:#666">Sin registros</td></tr>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #2D2418; }
  .head { margin-bottom: 14px; border-bottom: 2px solid #D4AF37; padding-bottom: 8px; }
  .brand { color: #D4AF37; font-size: 16px; font-weight: bold; margin: 0; }
  .title { font-size: 14px; margin: 4px 0 0; }
  .meta { color: #8A7B65; font-size: 10px; margin-top: 2px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #F5EBDB; color: #2D2418; border: 1px solid #E7DAC6; padding: 6px 8px; text-align: left; }
  td { border: 1px solid #E7DAC6; padding: 5px 8px; }
  tr:nth-child(even) td { background: #FFFEFA; }
</style>
</head>
<body>
  <div class="head">
    <p class="brand">Estilo Dorado</p>
    <p class="title">{$this->e($title)}</p>
    <p class="meta">Generado: {$generated} · Total filas: {$this->e((string) count($rows))}</p>
  </div>
  <table>
    <thead><tr>{$th}</tr></thead>
    <tbody>{$body}</tbody>
  </table>
</body>
</html>
HTML;
    }

    /** Escapa para HTML en heredoc ya interpolado con e() helper cuando se puede. */
    private function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function scalar(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i:s');
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        return (string) $v;
    }

    /**
     * Genera un .xlsx mínimo válido (una hoja) con ZipArchive.
     */
    private function buildXlsxBinary(array $headers, array $rows): string
    {
        $sheetRows = array_merge([$headers], $rows);
        $sheetXml = $this->sheetXml($sheetRows);

        $contentTypes = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;

        $rels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;

        $workbook = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Reporte" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;

        $workbookRels = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;

        $styles = <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/></font>
  </fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
  </fills>
  <borders count="1"><border/></borders>
  <cellStyleXfs count="1"><xf/></cellStyleXfs>
  <cellXfs count="2">
    <xf fontId="0"/>
    <xf fontId="1"/>
  </cellXfs>
</styleSheet>
XML;

        $tmp = tempnam(sys_get_temp_dir(), 'edxlsx');
        if ($tmp === false) {
            throw new \RuntimeException('No se pudo crear archivo temporal para XLSX');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new \RuntimeException('No se pudo abrir ZIP para XLSX');
        }

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = file_get_contents($tmp);
        @unlink($tmp);

        if ($binary === false) {
            throw new \RuntimeException('No se pudo leer el XLSX generado');
        }

        return $binary;
    }

    /**
     * @param  array<int, array<int, mixed>>  $matrix  primera fila = headers
     */
    private function sheetXml(array $matrix): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>';

        foreach ($matrix as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $xml .= '<row r="'.$rowNum.'">';
            foreach (array_values($row) as $cIdx => $value) {
                $col = $this->colName($cIdx);
                $ref = $col.$rowNum;
                $style = $rIdx === 0 ? ' s="1"' : '';
                $text = $this->xmlEscape($this->scalar($value));
                // inlineStr evita sharedStrings
                $xml .= '<c r="'.$ref.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.$text.'</t></is></c>';
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData></worksheet>';

        return $xml;
    }

    private function colName(int $index): string
    {
        $name = '';
        $n = $index;
        do {
            $name = chr(65 + ($n % 26)).$name;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);

        return $name;
    }

    private function xmlEscape(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
