<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE inventario MODIFY tipo_movimiento VARCHAR(20) NOT NULL");
        } catch (\Throwable $e) {
        }
        try {
            DB::statement("ALTER TABLE inventario MODIFY referencia_tipo VARCHAR(20) NULL");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        // no-op: no volver a enum estrecho
    }
};
