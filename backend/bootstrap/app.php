<?php

use App\Http\Middleware\AcessoAtivo;
use App\Http\Middleware\AcessoAtivoWeb;
use App\Http\Middleware\CabecalhosSeguranca;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\OnboardingCompleto;
use App\Http\Middleware\SenhaPadraoDefinida;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CabecalhosSeguranca::class,
            SetLocale::class,
            HandleInertiaRequests::class,
            // Comprador do checkout entra com senha padrao e define a sua antes de navegar
            SenhaPadraoDefinida::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'acesso.ativo' => AcessoAtivo::class,
            'acesso.web' => AcessoAtivoWeb::class,
            'onboarding.completo' => OnboardingCompleto::class,
        ]);

        // Em rotas de API nao ha redirect para "login" (que nao existe):
        // retorna null aqui e o handler responde 401 JSON.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : route('login'),
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Em rotas de API, nao autenticado sempre retorna 401 JSON
        // (evita o redirect para a rota "login" inexistente -> 500).
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Nao autenticado'], 401);
            }
        });
    })->create();
