<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Preferencia salva na conta (sincroniza entre dispositivos) >
        // sessao (visitante escolheu no login) > padrao do app.
        $locale = $request->user()?->locale
            ?? session('locale')
            ?? config('app.locale');

        // Backend usa pt_BR (traducoes do Filament); o front normaliza para 'pt'.
        // Locale desconhecido (ex.: APP_LOCALE=en) cai no padrao do produto: pt_BR.
        $mapa = ['pt' => 'pt_BR', 'pt_BR' => 'pt_BR', 'es' => 'es'];

        app()->setLocale($mapa[$locale] ?? 'pt_BR');

        return $next($request);
    }
}
