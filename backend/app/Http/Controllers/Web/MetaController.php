<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Meta;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Calendario de metas: criar meta com prazo, marcar feita / nao feita, excluir.
 */
class MetaController extends Controller
{
    public function index(Request $request)
    {
        $metas = $request->user()->metas()
            ->orderBy('prazo')->orderBy('id')
            ->get()
            ->map(fn (Meta $meta) => $this->formatar($meta))
            ->values();

        return Inertia::render('App/Metas', [
            'metas' => $metas,
            'hoje' => today()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'titulo' => 'required|string|min:2|max:120',
            'prazo' => 'required|date|after_or_equal:today',
        ]);

        $request->user()->metas()->create($dados);

        return back();
    }

    /** Alterna entre feita e nao feita. */
    public function update(Request $request, Meta $meta)
    {
        abort_unless($meta->user_id === $request->user()->id, 403);

        $request->validate(['concluida' => 'required|boolean']);

        $meta->update(['concluida_em' => $request->boolean('concluida') ? now() : null]);

        return back();
    }

    public function destroy(Request $request, Meta $meta)
    {
        abort_unless($meta->user_id === $request->user()->id, 403);

        $meta->delete();

        return back();
    }

    public static function formatar(Meta $meta): array
    {
        return [
            'id' => $meta->id,
            'titulo' => $meta->titulo,
            'prazo' => $meta->prazo->toDateString(),
            'concluida' => $meta->concluida_em !== null,
            'situacao' => $meta->situacao(),
            'dias_restantes' => (int) today()->diffInDays($meta->prazo, false),
        ];
    }
}
