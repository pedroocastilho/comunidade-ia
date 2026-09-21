<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Compradores entram com a senha padrao do checkout (12345678) e sao
 * obrigados a definir uma senha propria no primeiro acesso, antes de
 * navegar. O resultado do teste fica em sessao para nao recalcular o
 * hash a cada request.
 */
class SenhaPadraoDefinida
{
    public const SENHA_PADRAO = '12345678';

    private const ROTAS_LIVRES = ['senha.definir', 'senha.salvar', 'logout', 'idioma'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || in_array($request->route()?->getName(), self::ROTAS_LIVRES, true)) {
            return $next($request);
        }

        if (! $request->session()->has('senha_padrao')) {
            $request->session()->put('senha_padrao', Hash::check(self::SENHA_PADRAO, $user->password));
        }

        if ($request->session()->get('senha_padrao') === true) {
            return redirect()->route('senha.definir');
        }

        return $next($request);
    }
}
