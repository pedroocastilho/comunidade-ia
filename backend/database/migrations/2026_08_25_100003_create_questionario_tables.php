<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimensoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('questionario_perguntas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ordem');
            $table->string('tipo'); // texto | escala | escolha_unica
            $table->text('texto');
            $table->json('opcoes')->nullable();
            $table->boolean('obrigatoria')->default(true);
            $table->foreignId('dimensao_id')->nullable()->constrained('dimensoes')->nullOnDelete();
            $table->boolean('alimenta_memoria')->default(false);
            $table->timestamps();
        });

        Schema::create('questionario_respostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pergunta_id')->constrained('questionario_perguntas')->cascadeOnDelete();
            $table->text('valor');
            $table->timestamps();
            $table->unique(['user_id', 'pergunta_id']);
        });

        Schema::create('aura_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('score_global');
            $table->json('scores_dimensoes'); // [slug => int]
            $table->foreignId('dimensao_prioritaria_id')->nullable()->constrained('dimensoes')->nullOnDelete();
            $table->string('ponto_atencao')->nullable(); // slug da dimensao de menor score
            $table->json('padroes'); // [slug, ...]
            $table->dateTime('calculado_em');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aura_scores');
        Schema::dropIfExists('questionario_respostas');
        Schema::dropIfExists('questionario_perguntas');
        Schema::dropIfExists('dimensoes');
    }
};
