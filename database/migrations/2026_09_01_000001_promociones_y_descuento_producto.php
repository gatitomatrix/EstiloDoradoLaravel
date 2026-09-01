<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (! Schema::hasColumn('productos', 'descuento_pct')) {
                $table->decimal('descuento_pct', 5, 2)->default(0)->after('precio_venta');
            }
            if (! Schema::hasColumn('productos', 'oferta_hasta')) {
                $table->date('oferta_hasta')->nullable()->after('descuento_pct');
            }
        });

        if (! Schema::hasTable('promociones')) {
            Schema::create('promociones', function (Blueprint $table) {
                $table->id();
                $table->string('titulo', 120)->default('Campaña');
                $table->string('texto_cinta', 255)->nullable();
                $table->decimal('porcentaje', 5, 2)->default(0);
                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();
                $table->boolean('activo')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'oferta_hasta')) {
                $table->dropColumn('oferta_hasta');
            }
            if (Schema::hasColumn('productos', 'descuento_pct')) {
                $table->dropColumn('descuento_pct');
            }
        });
        Schema::dropIfExists('promociones');
    }
};
