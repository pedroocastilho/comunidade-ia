<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('produto_externo_id')->index();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->nullOnDelete();
            $table->foreignId('audio_id')->nullable()->constrained('audios')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'produto_externo_id']);
        });

        Schema::table('cursos', function (Blueprint $table) {
            $table->string('checkout_url')->nullable();
        });

        Schema::table('audios', function (Blueprint $table) {
            $table->string('checkout_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('audios', fn (Blueprint $table) => $table->dropColumn('checkout_url'));
        Schema::table('cursos', fn (Blueprint $table) => $table->dropColumn('checkout_url'));
        Schema::dropIfExists('compras');
    }
};
