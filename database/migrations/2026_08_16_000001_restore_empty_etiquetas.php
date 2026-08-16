<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('migrations/2026_08_10_000001_add_etiquetas_to_productos_table.php');
        if (! is_file($path)) {
            return;
        }

        $rows = DB::table('productos')
            ->whereNull('etiquetas')
            ->orWhere('etiquetas', '')
            ->pluck('id_producto');

        if ($rows->isEmpty()) {
            return;
        }

        $sql = database_path('sql/etiquetas_productos.sql');
        if (! is_file($sql)) {
            return;
        }

        $text = file_get_contents($sql) ?: '';
        if (preg_match_all("/UPDATE `productos` SET `etiquetas` = '([^']*)' WHERE `id_producto` = (\d+);/", $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $id = (int) $row[2];
                if (! $rows->contains($id)) {
                    continue;
                }
                DB::table('productos')->where('id_producto', $id)->where(function ($q) {
                    $q->whereNull('etiquetas')->orWhere('etiquetas', '');
                })->update(['etiquetas' => $row[1]]);
            }
        }
    }

    public function down(): void
    {
        // no-op: no borramos etiquetas restauradas
    }
};
