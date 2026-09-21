<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AulaResource;
use App\Models\Aula;
use App\Models\ProgressoAula;
use App\Services\BunnyService;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    /**
     * Mesmo gating da web (PainelController@aula): curso publicado e, se for
     * premium avulso, somente para quem comprou. A API entregava video e
     * download sem essas checagens.
     */
    private function autorizarAcesso(Request $request, Aula $aula): void
    {
        $curso = $aula->modulo?->curso;

        abort_if(! $curso || $curso->status !== 'publicado', 404);
        abort_if($curso->premium && ! $request->user()->comprou($curso->produto_externo_id), 403, 'Conteudo premium nao adquirido.');
    }

    public function show(Request $request, Aula $aula, BunnyService $bunny)
    {
        $this->autorizarAcesso($request, $aula);

        $progresso = ProgressoAula::where('user_id', $request->user()->id)
            ->where('aula_id', $aula->id)
            ->first();

        $aula->concluida = $progresso->concluida ?? false;
        $aula->posicao_segundos = $progresso->posicao_segundos ?? 0;
        $aula->embed_url = $aula->bunny_video_id ? $bunny->embedUrl($aula->bunny_video_id) : null;
        $aula->increment('views');

        return new AulaResource($aula);
    }

    public function concluir(Request $request, Aula $aula)
    {
        $this->autorizarAcesso($request, $aula);

        ProgressoAula::updateOrCreate(
            ['user_id' => $request->user()->id, 'aula_id' => $aula->id],
            ['concluida' => true],
        );

        return response()->json(['ok' => true]);
    }

    public function progresso(Request $request, Aula $aula)
    {
        $this->autorizarAcesso($request, $aula);

        $dados = $request->validate([
            'posicao_segundos' => ['required', 'integer', 'min:0'],
        ]);

        ProgressoAula::updateOrCreate(
            ['user_id' => $request->user()->id, 'aula_id' => $aula->id],
            ['posicao_segundos' => $dados['posicao_segundos']],
        );

        return response()->json(['ok' => true]);
    }

    public function download(Request $request, Aula $aula, BunnyService $bunny)
    {
        $this->autorizarAcesso($request, $aula);

        if (! $aula->bunny_video_id) {
            return response()->json(['message' => 'Aula sem video'], 404);
        }

        return response()->json(['url' => $bunny->downloadUrl($aula->bunny_video_id)]);
    }
}
