<?php

namespace App\Http\Middleware;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => app()->getLocale(),
            'auth' => [
                'user' => $request->user(),
            ],
            'categorias' => fn () => $request->user()
                ? Categoria::orderBy('ordem')->get(['nome', 'slug'])
                : [],
            // Barrinha de aviso no topo (estilo MeuFluxo): ultimo aviso publicado
            'aviso_topo' => fn () => $request->user()
                ? \App\Models\Aviso::whereNotNull('publicado_em')
                    ->where('publicado_em', '<=', now())
                    ->latest('publicado_em')
                    ->value('titulo')
                : null,
        ];
    }
}
