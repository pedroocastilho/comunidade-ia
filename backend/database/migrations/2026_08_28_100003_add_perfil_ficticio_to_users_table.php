<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perfis ficticios que movimentam o Circulo (posts, curtidas e comentarios
 * gerados pelo comando circulo:movimentar). Nunca fazem login e ficam fora
 * das metricas do admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('perfil_ficticio')->default(false)->after('assinatura_status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('perfil_ficticio');
        });
    }
};
