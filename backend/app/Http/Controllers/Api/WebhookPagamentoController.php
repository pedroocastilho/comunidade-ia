<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProcessadorWebhookPagamento;
use Illuminate\Http\Request;

class WebhookPagamentoController extends Controller
{
    public function receber(Request $request, string $plataforma, ProcessadorWebhookPagamento $processador)
    {
        if (! $this->autorizado($request, $plataforma)) {
            return response()->json(['message' => 'Nao autorizado'], 401);
        }

        // Payloads reais das plataformas tem poucos KB; corta abuso de armazenamento
        if (strlen($request->getContent()) > 100 * 1024) {
            return response()->json(['message' => 'Payload muito grande'], 413);
        }

        $processador->processar($plataforma, $request->all());

        // Sempre 200 para eventos recebidos: falhas ficam em webhooks_pagamento.erro
        return response()->json(['recebido' => true]);
    }

    /**
     * Autenticacao por plataforma:
     * - kiwify: ?signature= na URL = HMAC-SHA1 do corpo cru com o token da Kiwify
     * - hotmart: header X-HOTMART-HOTTOK igual ao hottok configurado
     * - qualquer plataforma: header X-Webhook-Token (contrato generico interno)
     * Cada verificacao so vale se o segredo correspondente estiver configurado.
     */
    private function autorizado(Request $request, string $plataforma): bool
    {
        // O token generico NAO vale para plataformas com autenticacao propria:
        // se ele vazasse, permitiria forjar eventos "kiwify"/"hotmart"
        $tokenGenerico = config('services.webhook_pagamento.token');
        if (! in_array($plataforma, ['kiwify', 'hotmart'], true)
            && $tokenGenerico && hash_equals($tokenGenerico, (string) $request->header('X-Webhook-Token'))) {
            return true;
        }

        if ($plataforma === 'kiwify') {
            $segredo = config('services.kiwify.webhook_token');
            $assinatura = (string) $request->query('signature');

            return $segredo && $assinatura !== ''
                && hash_equals(hash_hmac('sha1', $request->getContent(), $segredo), $assinatura);
        }

        if ($plataforma === 'hotmart') {
            $hottok = config('services.hotmart.hottok');

            return $hottok && hash_equals($hottok, (string) $request->header('X-HOTMART-HOTTOK'));
        }

        return false;
    }
}
