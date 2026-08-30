<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asistente_logs')) {
            return;
        }
        Schema::table('asistente_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('asistente_logs', 'productos')) {
                $table->string('productos', 500)->nullable()->after('n_productos');
            }
            if (! Schema::hasColumn('asistente_logs', 'queja_tipo')) {
                $table->string('queja_tipo', 40)->nullable()->after('productos');
            }
            if (! Schema::hasColumn('asistente_logs', 'urgencia')) {
                $table->boolean('urgencia')->default(false)->after('queja_tipo');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('asistente_logs')) {
            return;
        }
        Schema::table('asistente_logs', function (Blueprint $table) {
            foreach (['productos', 'queja_tipo', 'urgencia'] as $c) {
                if (Schema::hasColumn('asistente_logs', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
