<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'costo_envio')) {
                $table->decimal('costo_envio', 10, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('pedidos', 'envio_etiqueta')) {
                $table->string('envio_etiqueta', 160)->nullable()->after('costo_envio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'envio_etiqueta')) {
                $table->dropColumn('envio_etiqueta');
            }
            if (Schema::hasColumn('pedidos', 'costo_envio')) {
                $table->dropColumn('costo_envio');
            }
        });
    }
};
