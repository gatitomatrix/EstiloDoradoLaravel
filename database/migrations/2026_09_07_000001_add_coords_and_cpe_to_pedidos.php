<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $t) {
            if (! Schema::hasColumn('pedidos', 'lat_entrega')) {
                $t->decimal('lat_entrega', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('pedidos', 'lng_entrega')) {
                $t->decimal('lng_entrega', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('pedidos', 'comprobantes_json')) {
                $t->longText('comprobantes_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $t) {
            if (Schema::hasColumn('pedidos', 'lat_entrega')) {
                $t->dropColumn('lat_entrega');
            }
            if (Schema::hasColumn('pedidos', 'lng_entrega')) {
                $t->dropColumn('lng_entrega');
            }
        });
    }
};
