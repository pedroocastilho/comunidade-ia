<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('corpo');
            $table->string('imagem_url')->nullable();
            $table->foreignId('autor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('publicado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }
};
