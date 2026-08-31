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
            if (! Schema::hasColumn('asistente_logs', 'productos_json')) {
                $table->text('productos_json')->nullable()->after('productos');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('asistente_logs')) {
            return;
        }
        Schema::table('asistente_logs', function (Blueprint $table) {
            if (Schema::hasColumn('asistente_logs', 'productos_json')) {
                $table->dropColumn('productos_json');
            }
        });
    }
};
