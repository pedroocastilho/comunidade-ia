<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Denuncia;
use App\Models\Post;
use App\Models\PostComentario;
use App\Models\PostReacao;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * O Circulo: feed da comunidade (PRD secao 10, versao enxuta).
 */
class CirculoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $posts = Post::where('status', 'publicado')
            ->with(['user:id,name,apelido', 'comentarios.user:id,name,apelido'])
            ->withCount('reacoes')
            ->orderByDesc('fixado')
            ->latest('id')
            ->limit(50)
            ->get();

        $reagidos = PostReacao::where('user_id', $user->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        return Inertia::render('App/Circulo', [
            'posts' => $posts->map(fn ($post) => [
                'id' => $post->id,
                'autor' => $post->user->apelido ?? $post->user->name,
                'meu' => $post->user_id === $user->id,
                'corpo' => $post->corpo,
                'fixado' => $post->fixado,
                'reacoes' => $post->reacoes_count,
                'reagi' => in_array($post->id, $reagidos, true),
                'quando' => $post->created_at->diffForHumans(),
                'comentarios' => $post->comentarios->map(fn ($comentario) => [
                    'id' => $comentario->id,
                    'autor' => $comentario->user->apelido ?? $comentario->user->name,
                    'meu' => $comentario->user_id === $user->id,
                    'texto' => $comentario->texto,
                    'quando' => $comentario->created_at->diffForHumans(),
                ])->values(),
            ])->values(),
        ]);
    }

    public function publicar(Request $request)
    {
        $request->validate(['corpo' => 'required|string|min:2|max:2000']);

        Post::create([
            'user_id' => $request->user()->id,
            'corpo' => $request->input('corpo'),
        ]);

        return back();
    }

    public function reagir(Request $request, Post $post)
    {
        abort_unless($post->status === 'publicado', 404);

        $existente = PostReacao::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)->first();

        $existente ? $existente->delete() : PostReacao::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
        ]);

        return back();
    }

    public function comentar(Request $request, Post $post)
    {
        abort_unless($post->status === 'publicado', 404);

        $request->validate(['texto' => 'required|string|min:2|max:1000']);

        PostComentario::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'texto' => $request->input('texto'),
        ]);

        return back();
    }

    public function denunciar(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:post,comentario',
            'id' => 'required|integer',
            'motivo' => 'nullable|string|max:500',
        ]);

        $existe = $request->input('tipo') === 'post'
            ? Post::whereKey($request->integer('id'))->exists()
            : PostComentario::whereKey($request->integer('id'))->exists();
        abort_unless($existe, 404);

        Denuncia::firstOrCreate([
            'user_id' => $request->user()->id,
            'denunciavel_type' => $request->input('tipo'),
            'denunciavel_id' => $request->integer('id'),
        ], ['motivo' => $request->input('motivo')]);

        return back();
    }
}
