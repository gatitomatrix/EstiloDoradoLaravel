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
            if (! Schema::hasColumn('asistente_logs', 'id_cliente')) {
                $table->unsignedInteger('id_cliente')->nullable()->after('id');
            }
            if (! Schema::hasColumn('asistente_logs', 'cliente_nombre')) {
                $table->string('cliente_nombre', 120)->nullable();
            }
            if (! Schema::hasColumn('asistente_logs', 'cliente_email')) {
                $table->string('cliente_email', 150)->nullable();
            }
            if (! Schema::hasColumn('asistente_logs', 'celular')) {
                $table->string('celular', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('asistente_logs')) {
            return;
        }
        Schema::table('asistente_logs', function (Blueprint $table) {
            foreach (['id_cliente', 'cliente_nombre', 'cliente_email', 'celular'] as $c) {
                if (Schema::hasColumn('asistente_logs', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
