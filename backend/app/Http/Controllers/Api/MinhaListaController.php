<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CursoResource;
use App\Models\Curso;
use App\Models\MinhaLista;
use Illuminate\Http\Request;

class MinhaListaController extends Controller
{
    public function index(Request $request)
    {
        $cursoIds = MinhaLista::where('user_id', $request->user()->id)->pluck('curso_id');
        $cursos = Curso::whereIn('id', $cursoIds)->with(['categoria', 'instrutor'])->get();

        return CursoResource::collection($cursos);
    }

    public function store(Request $request, Curso $curso)
    {
        abort_unless($curso->status === 'publicado', 404);

        MinhaLista::firstOrCreate([
            'user_id' => $request->user()->id,
            'curso_id' => $curso->id,
        ]);

        return response()->json(['ok' => true], 201);
    }

    public function destroy(Request $request, Curso $curso)
    {
        MinhaLista::where('user_id', $request->user()->id)
            ->where('curso_id', $curso->id)
            ->delete();

        return response()->noContent();
    }
}
