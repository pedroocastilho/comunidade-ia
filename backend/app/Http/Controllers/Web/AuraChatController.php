<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\ExtrairMemorias;
use App\Models\AuraConversa;
use App\Models\AuraMensagem;
use App\Models\EventoAnalytics;
use App\Models\IaConfiguracao;
use App\Services\AnalyticsService;
use App\Services\AnthropicException;
use App\Services\AuraChatService;
use App\Services\ClassificadorConversa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Chat com a Aura (PRD secao 5).
 */
class AuraChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversas = $user->auraConversas()->latest('updated_at')->limit(20)->get(['id', 'titulo', 'updated_at']);
        $ativa = $conversas->first();

        return Inertia::render('App/Aura', [
            'conversas' => $conversas,
            'conversa_ativa' => $ativa?->id,
            'mensagens' => $ativa
                ? $ativa->mensagens()->oldest('id')->limit(50)->get(['id', 'papel', 'conteudo', 'created_at'])
                : [],
            'apelido' => $user->apelido ?? $user->name,
        ]);
    }

    public function mensagem(Request $request, AuraChatService $chat, ClassificadorConversa $classificador, AnalyticsService $analytics)
    {
        $request->validate([
            'texto' => 'required|string|max:4000',
            'conversa_id' => 'nullable|integer',
        ]);

        $user = $request->user();

        // Teto diario de tokens por usuario (PRD secao 14).
        $teto = (int) IaConfiguracao::valor('teto_diario_tokens', '50000');
        $gastoHoje = AuraMensagem::whereIn('conversa_id', $user->auraConversas()->pluck('id'))
            ->whereDate('created_at', today())
            ->sum('tokens');
        if ($gastoHoje >= $teto) {
            return response()->json([
                'erro' => 'Voce chegou ao limite de conversas de hoje. A Aura te espera amanha. ✦',
            ], 429);
        }

        $conversa = $request->filled('conversa_id')
            ? $user->auraConversas()->findOrFail($request->integer('conversa_id'))
            : $user->auraConversas()->create(['titulo' => Str::limit($request->input('texto'), 40)]);

        $jaConversouHoje = EventoAnalytics::where('nome', 'aura_chat_started')
            ->where('user_id', $user->id)->whereDate('created_at', today())->exists();
        if (! $jaConversouHoje) {
            $analytics->registrar('aura_chat_started', $user, ['nova_conversa' => ! $request->filled('conversa_id')]);
        }

        try {
            $resposta = $chat->enviar($user, $conversa, $request->input('texto'));
        } catch (AnthropicException) {
            return response()->json([
                'erro' => 'A Aura esta em silencio por um instante. Tente de novo em alguns segundos.',
            ], 503);
        }

        $conversa->touch();
        $classificador->classificar($conversa);

        if ($conversa->fresh()->classificacao !== 'crise') {
            ExtrairMemorias::dispatch($conversa);
        }

        return response()->json([
            'conversa_id' => $conversa->id,
            'mensagem' => $resposta->only(['id', 'papel', 'conteudo', 'created_at']),
        ]);
    }
}
