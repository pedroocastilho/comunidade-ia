<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebhookPagamento;
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
            // Kiwify/Hotmart/Hubla: mapear aqui quando a plataforma for definida.
            default => [
                'evento' => $payload['evento'] ?? null,
                'email' => $payload['email'] ?? null,
                'nome' => $payload['nome'] ?? null,
            ],
        };
    }

    private function aplicar(array $dados): void
    {
        $evento = $dados['evento'];
        $email = $dados['email'];

        if (! in_array($evento, self::EVENTOS, true)) {
            throw new \InvalidArgumentException("Evento desconhecido: ".($evento ?? 'nenhum'));
        }
        if (! $email) {
            throw new \InvalidArgumentException('Payload sem e-mail.');
        }

        if ($evento === 'assinatura_ativa') {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $dados['nome'] ?? Str::before($email, '@'),
                    'password' => Str::random(40), // acesso via "esqueci minha senha"
                ],
            );
            $user->update(['tem_acesso' => true, 'assinatura_status' => 'ativa']);

            return;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            throw new \RuntimeException("Usuario nao encontrado: {$email}");
        }

        match ($evento) {
            'assinatura_cancelada' => $user->update(['tem_acesso' => false, 'assinatura_status' => 'cancelada']),
            'pagamento_atrasado' => $user->update(['assinatura_status' => 'atrasada']), // carencia: mantem acesso
            'pagamento_regularizado' => $user->update(['tem_acesso' => true, 'assinatura_status' => 'ativa']),
        };
    }
}
