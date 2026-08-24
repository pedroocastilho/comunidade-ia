<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progresso_aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aula_id')->constrained('aulas')->cascadeOnDelete();
            $table->boolean('concluida')->default(false);
            $table->unsignedInteger('posicao_segundos')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'aula_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progresso_aulas');
    }
};
