<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProcessadorWebhookPagamento;
use Illuminate\Http\Request;

class WebhookPagamentoController extends Controller
{
    public function receber(Request $request, string $plataforma, ProcessadorWebhookPagamento $processador)
    {
        $tokenEsperado = config('services.webhook_pagamento.token');

        if (! $tokenEsperado || ! hash_equals($tokenEsperado, (string) $request->header('X-Webhook-Token'))) {
            return response()->json(['message' => 'Nao autorizado'], 401);
        }

        $processador->processar($plataforma, $request->all());

        // Sempre 200 para eventos recebidos: falhas ficam em webhooks_pagamento.erro
        return response()->json(['recebido' => true]);
    }
}
