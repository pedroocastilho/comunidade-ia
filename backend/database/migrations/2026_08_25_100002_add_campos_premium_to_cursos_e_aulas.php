<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->boolean('premium')->default(false);
            $table->string('produto_externo_id')->nullable();
        });

        Schema::table('aulas', function (Blueprint $table) {
            $table->boolean('premium')->default(false);
            $table->string('produto_externo_id')->nullable();
            $table->json('tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['premium', 'produto_externo_id']);
        });

        Schema::table('aulas', function (Blueprint $table) {
            $table->dropColumn(['premium', 'produto_externo_id', 'tags']);
        });
    }
};
