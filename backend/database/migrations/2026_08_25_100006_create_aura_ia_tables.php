<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aura_conversas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo')->nullable();
            $table->string('classificacao')->nullable(); // prosperidade | ... | duvida-plataforma | crise
            $table->timestamps();
        });

        Schema::create('aura_mensagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversa_id')->constrained('aura_conversas')->cascadeOnDelete();
            $table->string('papel'); // user | assistant
            $table->text('conteudo');
            $table->unsignedInteger('tokens')->nullable();
            $table->timestamps();
        });

        Schema::create('aura_memorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo'); // fato | preferencia | objetivo | contexto
            $table->text('conteudo');
            $table->string('origem'); // questionario | conversa | checkin
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('ia_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_configuracoes');
        Schema::dropIfExists('aura_memorias');
        Schema::dropIfExists('aura_mensagens');
        Schema::dropIfExists('aura_conversas');
    }
};
