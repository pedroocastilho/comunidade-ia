<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Gera URLs assinadas do Bunny Stream.
 *
 * - embedUrl: URL do player (iframe) com token de autenticacao do Stream.
 * - downloadUrl: URL do MP4 no CDN com token de autenticacao (download offline).
 *
 * A chave de token nunca vai para o cliente; so as URLs assinadas.
 */
class BunnyService
{
    private ?string $libraryId;

    private ?string $tokenKey;

    private ?string $cdnHostname;

    private int $ttl;

    public function __construct(
        ?string $libraryId = null,
        ?string $tokenKey = null,
        ?string $cdnHostname = null,
        ?int $ttl = null,
    ) {
        $this->libraryId = $libraryId ?? config('services.bunny.library_id');
        $this->tokenKey = $tokenKey ?? config('services.bunny.token_key');
        $this->cdnHostname = $cdnHostname ?? config('services.bunny.cdn_hostname');
        $this->ttl = $ttl ?? (int) config('services.bunny.url_ttl', 14400);
    }

    /**
     * URL do player (iframe embed) com token do Bunny Stream.
     * Token = sha256(chave + videoId + expires).
     */
    public function embedUrl(string $videoId, ?int $expires = null): string
    {
        $expires ??= Carbon::now()->addSeconds($this->ttl)->timestamp;
        $token = hash('sha256', $this->tokenKey.$videoId.$expires);

        return "https://iframe.mediadelivery.net/embed/{$this->libraryId}/{$videoId}"
            ."?token={$token}&expires={$expires}";
    }

    /**
     * URL de download do MP4 no CDN, com token de autenticacao do CDN.
     * Token = base64url(sha256_raw(chave + caminho + expires)).
     */
    public function downloadUrl(string $videoId, string $arquivo = 'play_720p.mp4', ?int $expires = null): string
    {
        $expires ??= Carbon::now()->addSeconds($this->ttl)->timestamp;
        $caminho = "/{$videoId}/{$arquivo}";
        $raw = hash('sha256', $this->tokenKey.$caminho.$expires, true);
        $token = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        return "https://{$this->cdnHostname}{$caminho}?token={$token}&expires={$expires}";
    }
}
