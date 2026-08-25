<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\ProgressoAudio;
use App\Services\AnalyticsService;
use App\Services\BunnyService;
use App\Services\JornadaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Biblioteca de audios (frequencias, meditacoes e rituais) — PRD secao 8.
 */
class AudioWebController extends Controller
{
    public function index(Request $request)
    {
        $q = Audio::where('status', 'publicado')->orderBy('ordem')->orderBy('titulo');

        if ($request->filled('tipo')) {
            $q->where('tipo', $request->query('tipo'));
        }
        if ($request->filled('busca')) {
            $q->where('titulo', 'like', '%'.$request->query('busca').'%');
        }

        $concluidos = $request->user()->progressoAudios()
            ->where('concluido', true)->pluck('audio_id')->all();

        return Inertia::render('App/Audios', [
            'audios' => $q->get(['id', 'tipo', 'titulo', 'descricao', 'capa_url', 'duracao'])
                ->map(fn ($a) => [...$a->toArray(), 'concluido' => in_array($a->id, $concluidos, true)]),
            'filtros' => $request->only(['tipo', 'busca']),
        ]);
    }

    public function player(Request $request, Audio $audio, BunnyService $bunny, AnalyticsService $analytics)
    {
        abort_unless($audio->status === 'publicado', 404);

        $progresso = $request->user()->progressoAudios()->where('audio_id', $audio->id)->first();

        $analytics->registrar('lesson_started', $request->user(), ['tipo' => 'audio', 'id' => $audio->id]);

        return Inertia::render('App/AudioPlayer', [
            'audio' => [
                'id' => $audio->id,
                'tipo' => $audio->tipo,
                'titulo' => $audio->titulo,
                'descricao' => $audio->descricao,
                'capa_url' => $audio->capa_url,
                'duracao' => $audio->duracao,
                'arquivo_url' => $audio->arquivo_url,
                'embed_url' => $audio->bunny_video_id ? $bunny->embedUrl($audio->bunny_video_id) : null,
                'posicao_segundos' => $progresso->posicao_segundos ?? 0,
                'concluido' => (bool) ($progresso->concluido ?? false),
            ],
        ]);
    }

    public function progresso(Request $request, Audio $audio, JornadaService $jornadas, AnalyticsService $analytics)
    {
        $request->validate([
            'posicao_segundos' => 'required|integer|min:0',
            'concluido' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $concluir = $request->boolean('concluido');

        $progresso = ProgressoAudio::firstOrNew(['user_id' => $user->id, 'audio_id' => $audio->id]);
        $jaEstavaConcluido = (bool) $progresso->concluido;
        $progresso->posicao_segundos = (int) $request->input('posicao_segundos');
        $progresso->concluido = $jaEstavaConcluido || $concluir;
        $progresso->save();

        if ($concluir && ! $jaEstavaConcluido) {
            $analytics->registrar('lesson_completed', $user, ['tipo' => 'audio', 'id' => $audio->id]);

            // Se for o ritual do dia, marca a atividade e tenta avancar.
            if ($jornada = $user->jornadaAtiva) {
                $dia = $jornadas->diaAtual($jornada);
                if ($dia && $dia->ritual_audio_id === $audio->id && ! $dia->ritual_concluido) {
                    $jornadas->concluirAtividade($dia, 'ritual');
                    if ($jornadas->avancarSeCompleto($jornada->fresh())) {
                        $analytics->registrar('journey_day_completed', $user, ['dia' => $jornada->fresh()->dia_atual]);
                    }
                }
            }
        }

        return back();
    }
}
