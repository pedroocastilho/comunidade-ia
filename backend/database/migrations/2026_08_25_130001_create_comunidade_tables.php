<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('corpo');
            $table->boolean('fixado')->default(false);
            $table->string('status')->default('publicado'); // publicado | oculto
            $table->timestamps();
            $table->index(['status', 'fixado', 'created_at']);
        });

        Schema::create('post_reacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
        });

        Schema::create('post_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('texto');
            $table->string('status')->default('publicado'); // publicado | oculto
            $table->timestamps();
        });

        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // quem denunciou
            $table->string('denunciavel_type'); // post | comentario
            $table->unsignedBigInteger('denunciavel_id');
            $table->string('motivo')->nullable();
            $table->boolean('resolvida')->default(false);
            $table->timestamps();
            $table->index(['denunciavel_type', 'denunciavel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denuncias');
        Schema::dropIfExists('post_comentarios');
        Schema::dropIfExists('post_reacoes');
        Schema::dropIfExists('posts');
    }
};
