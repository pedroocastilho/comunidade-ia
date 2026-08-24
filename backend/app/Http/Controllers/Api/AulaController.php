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
    public function show(Request $request, Aula $aula, BunnyService $bunny)
    {
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
        ProgressoAula::updateOrCreate(
            ['user_id' => $request->user()->id, 'aula_id' => $aula->id],
            ['concluida' => true],
        );

        return response()->json(['ok' => true]);
    }

    public function progresso(Request $request, Aula $aula)
    {
        $dados = $request->validate([
            'posicao_segundos' => ['required', 'integer', 'min:0'],
        ]);

        ProgressoAula::updateOrCreate(
            ['user_id' => $request->user()->id, 'aula_id' => $aula->id],
            ['posicao_segundos' => $dados['posicao_segundos']],
        );

        return response()->json(['ok' => true]);
    }

    public function download(Aula $aula, BunnyService $bunny)
    {
        if (! $aula->bunny_video_id) {
            return response()->json(['message' => 'Aula sem video'], 404);
        }

        return response()->json(['url' => $bunny->downloadUrl($aula->bunny_video_id)]);
    }
}
