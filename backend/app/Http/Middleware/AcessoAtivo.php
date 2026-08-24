<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AcessoAtivo
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $expirado = $user && $user->acesso_expira_em && $user->acesso_expira_em->isPast();

        if (! $user || ! $user->tem_acesso || $expirado) {
            return response()->json(['message' => 'Acesso inativo'], 403);
        }

        return $next($request);
    }
}
