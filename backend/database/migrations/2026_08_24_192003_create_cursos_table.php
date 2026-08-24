<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('instrutor_id')->nullable()->constrained('instrutores')->nullOnDelete();
            $table->string('titulo');
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->string('capa_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->unsignedInteger('duracao_total')->default(0);
            $table->unsignedInteger('ordem')->default(0);
            $table->enum('status', ['rascunho', 'publicado'])->default('rascunho');
            $table->boolean('destaque')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
