<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Forca o questionario de onboarding no primeiro acesso (PRD secao 3).
 * Aplicar depois de auth + acesso.web, nas rotas do painel.
 */
class OnboardingCompleto
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->onboarding_completo_em === null) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
