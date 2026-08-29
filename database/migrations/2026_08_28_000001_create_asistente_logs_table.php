<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistente_logs', function (Blueprint $table) {
            $table->id();
            $table->string('mensaje', 500);
            $table->string('tipo', 32)->default('consulta');
            $table->unsignedTinyInteger('n_productos')->default(0);
            $table->boolean('whatsapp')->default(false);
            $table->string('driver', 16)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistente_logs');
    }
};
