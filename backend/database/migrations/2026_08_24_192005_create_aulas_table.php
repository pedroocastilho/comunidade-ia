<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('bunny_library_id')->nullable();
            $table->string('bunny_video_id')->nullable();
            $table->unsignedInteger('duracao')->default(0);
            $table->string('material_url')->nullable();
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('is_bonus')->default(false);
            $table->dateTime('liberada_em')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
