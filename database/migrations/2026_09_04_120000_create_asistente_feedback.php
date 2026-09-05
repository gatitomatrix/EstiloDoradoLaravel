<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asistente_feedback')) {
            return;
        }
        Schema::create('asistente_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_producto');
            $table->string('voto', 8);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['id_producto', 'voto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistente_feedback');
    }
};
