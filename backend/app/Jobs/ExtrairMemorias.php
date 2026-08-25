<?php

namespace App\Jobs;

use App\Models\AuraConversa;
use App\Services\AnthropicService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

/**
 * Extrai fatos duraveis da conversa e grava em aura_memorias (PRD secao 5).
 * Roda apos cada troca; falha silenciosa (memoria e enriquecimento, nao fluxo critico).
 */
class ExtrairMemorias implements ShouldQueue
{
    use Queueable;

    private const MAX_MENSAGENS = 10;

    private const TIPOS_VALIDOS = ['fato', 'preferencia', 'objetivo', 'contexto'];

    public function __construct(public AuraConversa $conversa) {}

    public function handle(AnthropicService $anthropic): void
    {
        $conversa = $this->conversa->fresh();
        $user = $conversa->user;

        $transcricao = $conversa->mensagens()
            ->latest('id')->limit(self::MAX_MENSAGENS)->get()->reverse()
            ->map(fn ($m) => ($m->papel === 'user' ? 'Usuario: ' : 'Aura: ').$m->conteudo)
            ->implode("\n");

        $existentes = $user->auraMemorias()->where('ativo', true)->pluck('conteudo')->implode("\n- ");

        try {
            $resposta = $anthropic->mensagens([
                'system' => 'Você extrai fatos DURÁVEIS sobre o usuário a partir de uma conversa (nome de pessoas próximas, situação de vida, preferências, objetivos). Ignore estados passageiros do dia. Responda APENAS um array JSON de objetos {"tipo": "fato|preferencia|objetivo|contexto", "conteudo": "frase curta"}. Se não houver nada novo, responda [].'
                    .($existentes ? "\n\nJá registrado (não repita):\n- ".$existentes : ''),
                'messages' => [['role' => 'user', 'content' => $transcricao]],
                'max_tokens' => 512,
                'temperature' => 0.0,
            ]);
        } catch (\Throwable) {
            return; // memoria nunca derruba o chat
        }

        $texto = collect($resposta['content'] ?? [])->firstWhere('type', 'text')['text'] ?? '[]';
        $json = Str::of($texto)->between('[', ']')->prepend('[')->append(']');
        $itens = json_decode((string) $json, true);

        if (! is_array($itens)) {
            return;
        }

        foreach ($itens as $item) {
            $conteudo = trim((string) ($item['conteudo'] ?? ''));
            $tipo = in_array($item['tipo'] ?? '', self::TIPOS_VALIDOS, true) ? $item['tipo'] : 'contexto';

            if ($conteudo === '' || mb_strlen($conteudo) > 500) {
                continue;
            }

            $user->auraMemorias()->firstOrCreate(
                ['conteudo' => $conteudo],
                ['tipo' => $tipo, 'origem' => 'conversa', 'ativo' => true],
            );
        }
    }
}
