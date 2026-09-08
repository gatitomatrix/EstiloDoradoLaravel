<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedidos')) {
            return;
        }
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'observacion')) {
                $table->text('observacion')->nullable();
            }
            if (! Schema::hasColumn('pedidos', 'nota_admin')) {
                $table->text('nota_admin')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pedidos', 'nota_admin')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->dropColumn('nota_admin');
            });
        }
    }
};
