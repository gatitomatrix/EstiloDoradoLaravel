<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clientes')) {
            return;
        }
        if (! Schema::hasColumn('clientes', 'auth_provider')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->string('auth_provider', 20)->default('local')->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clientes', 'auth_provider')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropColumn('auth_provider');
            });
        }
    }
};
