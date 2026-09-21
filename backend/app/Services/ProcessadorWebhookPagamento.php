<?php

namespace App\Services;

use App\Http\Middleware\SenhaPadraoDefinida;
use App\Models\Audio;
use App\Models\Compra;
use App\Models\Curso;
use App\Models\User;
use App\Models\WebhookPagamento;
use App\Notifications\BoasVindasAssinante;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Traduz webhooks de pagamento em eventos internos (PRD secao 16).
 * Adapter por plataforma: enquanto a plataforma definitiva nao e escolhida,
 * o adapter "generico" espera {evento, email, nome?}. Novos adapters mapeiam
 * o payload da plataforma para esse mesmo contrato.
 */
class ProcessadorWebhookPagamento
{
    public const EVENTOS = [
        'assinatura_ativa',
        'assinatura_cancelada',
        'pagamento_atrasado',
        'pagamento_regularizado',
        'compra_aprovada', // conteudo premium avulso (PRD secao 9)
        'compra_revogada', // reembolso/chargeback de compra avulsa
        'ignorar', // evento recebido e valido, mas sem efeito na plataforma
    ];

    /**
     * Grava o payload cru e processa. Nunca lanca excecao para fora
     * (a plataforma de pagamento nao deve reentregar para sempre).
     */
    public function processar(string $plataforma, array $payload): WebhookPagamento
    {
        $dados = $this->adaptar($plataforma, $payload);

        $registro = WebhookPagamento::create([
            'plataforma' => $plataforma,
            'evento' => $dados['evento'] ?? 'desconhecido',
            'email' => $dados['email'] ?? null,
            'payload' => $payload,
        ]);

        try {
            $this->aplicar($dados);
            $registro->update(['processado' => true]);
        } catch (\Throwable $e) {
            $registro->update(['erro' => $e->getMessage()]);
        }

        return $registro;
    }

    /**
     * Adapter por plataforma -> contrato interno {evento, email, nome?}.
     */
    private function adaptar(string $plataforma, array $payload): array
    {
        return match ($plataforma) {
            'kiwify' => $this->adaptarKiwify($payload),
            'hotmart' => $this->adaptarHotmart($payload),
            default => [
                'evento' => $payload['evento'] ?? null,
                'email' => $payload['email'] ?? null,
                'nome' => $payload['nome'] ?? null,
                'produto_externo_id' => $payload['produto_externo_id'] ?? null,
                'payload' => $payload,
            ],
        };
    }

    /**
     * Kiwify: evento vem em webhook_event_type; comprador em Customer;
     * produto em Product.product_id. product_type "membership" = assinatura,
     * qualquer outro = compra avulsa.
     */
    private function adaptarKiwify(array $payload): array
    {
        $tipo = $payload['webhook_event_type'] ?? null;
        $assinatura = ($payload['product_type'] ?? null) === 'membership'
            || isset($payload['Subscription']);

        $evento = match ($tipo) {
            'order_approved', 'subscription_renewed' => $assinatura ? 'assinatura_ativa' : 'compra_aprovada',
            'subscription_late' => 'pagamento_atrasado',
            'subscription_canceled' => 'assinatura_cancelada',
            'order_refunded', 'refunded', 'chargeback' => $assinatura ? 'assinatura_cancelada' : 'compra_revogada',
            // Boleto/pix gerados e compras recusadas nao mudam acesso
            'billet_created', 'pix_created', 'order_rejected' => 'ignorar',
            default => null,
        };

        // Reforco: alguns eventos chegam com status final no order_status
        if ($evento === null && in_array($payload['order_status'] ?? null, ['refunded', 'chargedback'], true)) {
            $evento = $assinatura ? 'assinatura_cancelada' : 'compra_revogada';
        }

        return [
            'evento' => $evento,
            'email' => $payload['Customer']['email'] ?? null,
            'nome' => $payload['Customer']['full_name'] ?? $payload['Customer']['first_name'] ?? null,
            'produto_externo_id' => $payload['Product']['product_id'] ?? null,
            'payload' => $payload,
        ];
    }

    /**
     * Hotmart (webhook 2.0): evento em event; comprador em data.buyer ou
     * data.subscriber; produto em data.product.id. Presenca de subscription
     * no payload = assinatura, ausencia = compra avulsa.
     */
    private function adaptarHotmart(array $payload): array
    {
        $dados = $payload['data'] ?? [];
        $assinatura = isset($dados['subscription']);

        $evento = match ($payload['event'] ?? null) {
            'PURCHASE_APPROVED', 'PURCHASE_COMPLETE' => $assinatura ? 'assinatura_ativa' : 'compra_aprovada',
            'PURCHASE_DELAYED' => 'pagamento_atrasado',
            'SUBSCRIPTION_CANCELLATION' => 'assinatura_cancelada',
            'PURCHASE_CANCELED', 'PURCHASE_REFUNDED', 'PURCHASE_CHARGEBACK' => $assinatura ? 'assinatura_cancelada' : 'compra_revogada',
            // Disputa aberta ainda nao e estorno: mantem acesso, marca atraso
            'PURCHASE_PROTEST' => 'pagamento_atrasado',
            // Sem efeito no acesso
            'PURCHASE_BILLET_PRINTED', 'PURCHASE_EXPIRED', 'PURCHASE_OUT_OF_SHOPPING_CART',
            'UPDATE_SUBSCRIPTION_CHARGE_DATE', 'SWITCH_PLAN', 'CLUB_FIRST_ACCESS', 'CLUB_MODULE_COMPLETED' => 'ignorar',
            default => null,
        };

        $email = $dados['buyer']['email']
            ?? $dados['subscriber']['email']
            ?? $dados['subscription']['user']['email']
            ?? null;

        $produto = $dados['product']['id'] ?? null;

        return [
            'evento' => $evento,
            'email' => $email,
            'nome' => $dados['buyer']['name'] ?? $dados['subscriber']['name'] ?? null,
            'produto_externo_id' => $produto !== null ? (string) $produto : null,
            'payload' => $payload,
        ];
    }

    private function aplicar(array $dados): void
    {
        $evento = $dados['evento'];
        $email = $dados['email'];

        if (! in_array($evento, self::EVENTOS, true)) {
            throw new \InvalidArgumentException('Evento desconhecido: '.($evento ?? 'nenhum'));
        }

        if ($evento === 'ignorar') {
            return;
        }

        if (! $email) {
            throw new \InvalidArgumentException('Payload sem e-mail.');
        }

        if ($evento === 'assinatura_ativa') {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $dados['nome'] ?? Str::before($email, '@'),
                    // Senha padrao do checkout; o primeiro login obriga a definir a propria
                    'password' => SenhaPadraoDefinida::SENHA_PADRAO,
                ],
            );
            $user->update(['tem_acesso' => true, 'assinatura_status' => 'ativa']);

            if ($user->wasRecentlyCreated) {
                $this->enviarBoasVindas($user);
            }

            return;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            throw new \RuntimeException("Usuario nao encontrado: {$email}");
        }

        if ($evento === 'compra_aprovada') {
            $this->liberarCompra($user, $dados);

            return;
        }

        if ($evento === 'compra_revogada') {
            $this->revogarCompra($user, $dados);

            return;
        }

        match ($evento) {
            'assinatura_cancelada' => $user->update(['tem_acesso' => false, 'assinatura_status' => 'cancelada']),
            'pagamento_atrasado' => $user->update(['assinatura_status' => 'atrasada']), // carencia: mantem acesso
            'pagamento_regularizado' => $user->update(['tem_acesso' => true, 'assinatura_status' => 'ativa']),
        };
    }

    /**
     * E-mail de boas-vindas com link para o comprador criar a senha.
     * Falha de SMTP nao derruba o webhook: o acesso ja foi liberado e o
     * membro ainda consegue entrar pelo "esqueci minha senha".
     */
    private function enviarBoasVindas(User $user): void
    {
        try {
            $user->notify(new BoasVindasAssinante);
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar boas-vindas', [
                'user_id' => $user->id,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Libera um conteudo premium avulso comprado no checkout externo.
     */
    private function liberarCompra(User $user, array $dados): void
    {
        $produtoId = $dados['produto_externo_id'] ?? null;
        if (! $produtoId) {
            throw new \InvalidArgumentException('Compra sem produto_externo_id.');
        }

        $curso = Curso::where('produto_externo_id', $produtoId)->first();
        $audio = $curso ? null : Audio::where('produto_externo_id', $produtoId)->first();

        if (! $curso && ! $audio) {
            throw new \RuntimeException("Produto premium nao encontrado: {$produtoId}");
        }

        Compra::firstOrCreate(
            ['user_id' => $user->id, 'produto_externo_id' => $produtoId],
            ['curso_id' => $curso?->id, 'audio_id' => $audio?->id, 'payload' => $dados['payload'] ?? null],
        );

        app(AnalyticsService::class)->registrar('premium_purchased', $user, ['produto' => $produtoId]);
    }

    /**
     * Reembolso/chargeback de compra avulsa: remove a liberacao do conteudo.
     * Idempotente: sem compra correspondente, nao ha o que revogar.
     */
    private function revogarCompra(User $user, array $dados): void
    {
        $produtoId = $dados['produto_externo_id'] ?? null;
        if (! $produtoId) {
            throw new \InvalidArgumentException('Revogacao sem produto_externo_id.');
        }

        Compra::where('user_id', $user->id)
            ->where('produto_externo_id', $produtoId)
            ->delete();
    }
}
