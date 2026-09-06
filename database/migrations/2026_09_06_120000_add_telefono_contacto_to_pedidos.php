<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pedidos', 'telefono_contacto')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->string('telefono_contacto', 20)->nullable()->after('direccion_entrega');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'telefono_contacto')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropColumn('telefono_contacto');
            });
        }
    }
};
