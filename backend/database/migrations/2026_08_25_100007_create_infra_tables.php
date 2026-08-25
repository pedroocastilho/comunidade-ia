<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nome');
            $table->json('propriedades')->nullable();
            $table->dateTime('created_at');
            $table->index(['nome', 'created_at']);
        });

        Schema::create('webhooks_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('plataforma'); // kiwify | hotmart | hubla | ...
            $table->string('evento'); // assinatura_ativa | assinatura_cancelada | pagamento_atrasado | pagamento_regularizado
            $table->string('email')->nullable();
            $table->json('payload');
            $table->boolean('processado')->default(false);
            $table->text('erro')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks_pagamento');
        Schema::dropIfExists('eventos_analytics');
    }
};
