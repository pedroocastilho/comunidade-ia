<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apelido')->nullable();
            $table->string('objetivo_principal')->nullable(); // slug de dimensao
            $table->string('objetivo_secundario')->nullable();
            $table->string('tempo_disponivel')->nullable(); // 5-10 | 15-20 | 30+
            $table->dateTime('onboarding_completo_em')->nullable();
            $table->string('assinatura_status')->default('manual'); // ativa | cancelada | atrasada | manual
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'apelido',
                'objetivo_principal',
                'objetivo_secundario',
                'tempo_disponivel',
                'onboarding_completo_em',
                'assinatura_status',
            ]);
        });
    }
};
