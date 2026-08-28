<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Genero gramatical (f | m), usado hoje pelos perfis ficticios do Circulo para
 * nao publicar frases no feminino em nome de um perfil masculino (e vice-versa).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('genero', 1)->nullable()->after('perfil_ficticio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('genero');
        });
    }
};
