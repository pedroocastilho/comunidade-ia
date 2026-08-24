<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvisoResource;
use App\Http\Resources\CursoResource;
use App\Models\Aula;
use App\Models\Aviso;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\ProgressoAula;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $destaque = Curso::where('status', 'publicado')
            ->where('destaque', true)
            ->with(['categoria', 'instrutor'])
            ->first();

        $aulaIds = ProgressoAula::where('user_id', $userId)
            ->where('concluida', false)
            ->where('posicao_segundos', '>', 0)
            ->latest('updated_at')
            ->pluck('aula_id');

        $cursoIds = Aula::whereIn('aulas.id', $aulaIds)
            ->join('modulos', 'aulas.modulo_id', '=', 'modulos.id')
            ->pluck('modulos.curso_id')
            ->unique()
            ->values();

        $continuar = Curso::whereIn('id', $cursoIds)
            ->with(['categoria', 'instrutor'])
            ->get();

        $trilhas = Categoria::orderBy('ordem')->get()->map(fn ($cat) => [
            'categoria' => ['id' => $cat->id, 'nome' => $cat->nome, 'slug' => $cat->slug],
            'cursos' => CursoResource::collection(
                Curso::where('status', 'publicado')
                    ->where('categoria_id', $cat->id)
                    ->with('instrutor')
                    ->orderBy('ordem')
                    ->limit(10)
                    ->get()
            ),
        ]);

        $avisos = Aviso::whereNotNull('publicado_em')
            ->where('publicado_em', '<=', now())
            ->latest('publicado_em')
            ->limit(5)
            ->get();

        return response()->json([
            'destaque' => $destaque ? new CursoResource($destaque) : null,
            'continuar_assistindo' => CursoResource::collection($continuar),
            'trilhas' => $trilhas,
            'avisos' => AvisoResource::collection($avisos),
        ]);
    }
}
