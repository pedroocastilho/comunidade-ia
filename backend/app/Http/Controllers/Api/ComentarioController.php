<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComentarioResource;
use App\Models\Aula;
use App\Models\Comentario;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    public function index(Aula $aula)
    {
        $comentarios = $aula->comentarios()
            ->where('aprovado', true)
            ->with('user')
            ->latest()
            ->get();

        return ComentarioResource::collection($comentarios);
    }

    public function store(Request $request, Aula $aula)
    {
        $dados = $request->validate([
            'texto' => ['required', 'string', 'max:2000'],
            'comentario_pai_id' => ['nullable', 'exists:comentarios,id'],
        ]);

        $comentario = $aula->comentarios()->create([
            'user_id' => $request->user()->id,
            'texto' => $dados['texto'],
            'comentario_pai_id' => $dados['comentario_pai_id'] ?? null,
        ]);

        $comentario->load('user');

        return (new ComentarioResource($comentario))->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Comentario $comentario)
    {
        abort_if($comentario->user_id !== $request->user()->id, 403);

        $comentario->delete();

        return response()->noContent();
    }
}
