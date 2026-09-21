<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Cabecalhos de seguranca da resposta web.
 * X-Frame-Options, X-Content-Type-Options e Referrer-Policy ja vem do nginx
 * (CloudPanel); aqui entram os que faltam la.
 *
 * CSP: script-src precisa de 'unsafe-inline' por causa do @routes (Ziggy) e
 * style-src por causa dos estilos inline do Vue. Mesmo assim a politica
 * bloqueia scripts de dominios externos, objetos e embutir o site em iframes
 * de terceiros. frame-src libera o player do Bunny Stream.
 */
class CabecalhosSeguranca
{
    private const CSP = "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline'; "
        ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        ."font-src 'self' https://fonts.gstatic.com; "
        ."img-src 'self' data: https:; "
        ."media-src 'self' https:; "
        .'frame-src https://iframe.mediadelivery.net; '
        ."connect-src 'self'; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        ."form-action 'self'; "
        ."frame-ancestors 'self'";

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // CSP so em HTML (nao adianta em JSON) e fora do admin (Filament usa
        // assets proprios; endurecer la exige nonce e quebraria o painel)
        if (! $request->is('admin*') && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Content-Security-Policy', self::CSP);
        }

        return $response;
    }
}
