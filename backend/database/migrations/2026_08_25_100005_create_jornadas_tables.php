<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jornada_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimensao_id')->constrained('dimensoes')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->unsignedInteger('duracao_dias')->default(30);
            $table->string('status')->default('rascunho'); // rascunho | publicado
            $table->timestamps();
        });

        Schema::create('jornada_template_dias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('jornada_templates')->cascadeOnDelete();
            $table->unsignedInteger('dia'); // 1..duracao_dias
            $table->string('etapa'); // titulo do pilar (ex.: Consciencia)
            $table->foreignId('ritual_audio_id')->nullable()->constrained('audios')->nullOnDelete();
            $table->foreignId('aula_id')->nullable()->constrained('aulas')->nullOnDelete();
            $table->text('acao_texto')->nullable();
            $table->json('equivalentes')->nullable(); // pool de substitutos por tipo
            $table->json('variante_curta')->nullable(); // atividades alternativas p/ tempo 5-10
            $table->timestamps();
            $table->unique(['template_id', 'dia']);
        });

        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('jornada_templates')->cascadeOnDelete();
            $table->unsignedInteger('dia_atual')->default(1);
            $table->string('status')->default('ativa'); // ativa | concluida
            $table->dateTime('iniciada_em');
            $table->date('ultimo_avanco_em')->nullable(); // trava de 1 avanco por dia-calendario
            $table->timestamps();
        });

        Schema::create('jornada_dias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jornada_id')->constrained('jornadas')->cascadeOnDelete();
            $table->unsignedInteger('dia');
            $table->string('etapa');
            $table->foreignId('ritual_audio_id')->nullable()->constrained('audios')->nullOnDelete();
            $table->foreignId('aula_id')->nullable()->constrained('aulas')->nullOnDelete();
            $table->text('acao_texto')->nullable();
            $table->boolean('ritual_concluido')->default(false);
            $table->boolean('aula_concluida')->default(false);
            $table->boolean('acao_concluida')->default(false);
            $table->boolean('adaptado_por_ia')->default(false);
            $table->json('origem_adaptacao')->nullable(); // log: gatilho, motivo, substituto
            $table->dateTime('concluido_em')->nullable();
            $table->timestamps();
            $table->unique(['jornada_id', 'dia']);
        });

        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jornada_dia_id')->nullable()->constrained('jornada_dias')->nullOnDelete();
            $table->unsignedTinyInteger('humor'); // 1..5
            $table->text('texto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
        Schema::dropIfExists('jornada_dias');
        Schema::dropIfExists('jornadas');
        Schema::dropIfExists('jornada_template_dias');
        Schema::dropIfExists('jornada_templates');
    }
};
