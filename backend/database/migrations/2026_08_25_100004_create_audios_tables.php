<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // frequencia | meditacao | ritual
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('arquivo_url')->nullable();
            $table->string('bunny_library_id')->nullable();
            $table->string('bunny_video_id')->nullable(); // Bunny Stream tambem serve audio
            $table->string('capa_url')->nullable();
            $table->unsignedInteger('duracao')->default(0); // segundos
            $table->json('tags')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->string('status')->default('rascunho'); // rascunho | publicado
            $table->boolean('premium')->default(false);
            $table->string('produto_externo_id')->nullable();
            $table->timestamps();
        });

        Schema::create('progresso_audios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('audio_id')->constrained('audios')->cascadeOnDelete();
            $table->boolean('concluido')->default(false);
            $table->unsignedInteger('posicao_segundos')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'audio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progresso_audios');
        Schema::dropIfExists('audios');
    }
};
