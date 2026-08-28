<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Metas pessoais com prazo (calendario de metas): a pessoa escolhe ate que dia
 * quer cumprir algo e depois marca se fez ou nao.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->date('prazo');
            $table->dateTime('concluida_em')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'prazo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
