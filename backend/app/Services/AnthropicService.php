<?php

namespace App\Services;

use App\Models\IaConfiguracao;
use Illuminate\Support\Facades\Http;

/**
 * Cliente da API Anthropic (/v1/messages).
 * Modelo, temperatura e max_tokens vem de ia_configuracoes (editaveis no admin).
 */
class AnthropicService
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const VERSAO = '2023-06-01';

    /**
     * @param  array  $params  ['system' => string, 'messages' => array, 'tools' => array?, 'max_tokens' => int?, 'temperature' => float?]
     * @return array resposta JSON decodificada da API
     *
     * @throws AnthropicException
     */
    public function mensagens(array $params): array
    {
        $payload = [
            'model' => IaConfiguracao::valor('modelo', 'claude-haiku-4-5-20251001'),
            'max_tokens' => $params['max_tokens'] ?? (int) IaConfiguracao::valor('max_tokens', '1024'),
            'temperature' => $params['temperature'] ?? (float) IaConfiguracao::valor('temperatura', '1.0'),
            'system' => $params['system'],
            'messages' => $params['messages'],
        ];

        if (! empty($params['tools'])) {
            $payload['tools'] = $params['tools'];
        }

        $resposta = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => self::VERSAO,
        ])
            ->timeout(60)
            ->post(self::ENDPOINT, $payload);

        if ($resposta->failed()) {
            throw new AnthropicException(
                'Erro na API Anthropic ('.$resposta->status().'): '
                .($resposta->json('error.message') ?? $resposta->body())
            );
        }

        return $resposta->json();
    }
}
