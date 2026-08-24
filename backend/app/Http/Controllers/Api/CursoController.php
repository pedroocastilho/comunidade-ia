<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CursoDetalhadoResource;
use App\Http\Resources\CursoResource;
use App\Models\Curso;
use App\Models\ProgressoAula;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $q = Curso::query()->where('status', 'publicado')->with(['categoria', 'instrutor']);

        if ($request->filled('categoria')) {
            $slug = $request->query('categoria');
            $q->whereHas('categoria', fn ($c) => $c->where('slug', $slug)->orWhere('id', $slug));
        }

        if ($request->filled('busca')) {
            $q->where('titulo', 'like', '%'.$request->query('busca').'%');
        }

        $request->boolean('em_alta') ? $q->orderByDesc('views') : $q->orderBy('ordem');

        return CursoResource::collection($q->get());
    }

    public function emAlta()
    {
        $cursos = Curso::where('status', 'publicado')
            ->with(['categoria', 'instrutor'])
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        return CursoResource::collection($cursos);
    }

    public function show(Request $request, string $slug)
    {
        $curso = Curso::where('status', 'publicado')
            ->where('slug', $slug)
            ->with(['categoria', 'instrutor', 'modulos.aulas'])
            ->firstOrFail();

        $this->anexarProgresso($curso, $request->user()->id);

        return new CursoDetalhadoResource($curso);
    }

    private function anexarProgresso(Curso $curso, int $userId): void
    {
        $aulaIds = $curso->modulos->flatMap->aulas->pluck('id');

        $mapa = ProgressoAula::where('user_id', $userId)
            ->whereIn('aula_id', $aulaIds)
            ->get()
            ->keyBy('aula_id');

        $concluidas = 0;
        foreach ($curso->modulos as $modulo) {
            foreach ($modulo->aulas as $aula) {
                $progresso = $mapa->get($aula->id);
                $aula->concluida = $progresso->concluida ?? false;
                $aula->posicao_segundos = $progresso->posicao_segundos ?? 0;
                if ($aula->concluida) {
                    $concluidas++;
                }
            }
        }

        $total = $aulaIds->count();
        $curso->progresso_percentual = $total ? (int) round($concluidas / $total * 100) : 0;
    }
}
