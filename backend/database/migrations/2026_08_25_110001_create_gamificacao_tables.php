<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('xp')->default(0);
        });

        Schema::create('conquistas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nome');
            $table->string('descricao');
            $table->string('icone')->default('✦');
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('conquista_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conquista_id')->constrained('conquistas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('conquistado_em');
            $table->unique(['conquista_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conquista_user');
        Schema::dropIfExists('conquistas');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('xp');
        });
    }
};
