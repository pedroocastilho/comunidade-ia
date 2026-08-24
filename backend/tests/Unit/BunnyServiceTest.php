<?php

namespace Tests\Unit;

use App\Services\BunnyService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class BunnyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_embed_url_gera_token_e_expires_deterministicos(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(1_000_000));
        $bunny = new BunnyService(libraryId: '633749', tokenKey: 'chave-secreta', cdnHostname: 'cdn.exemplo.net', ttl: 3600);

        $expires = 1_000_000 + 3600;
        $tokenEsperado = hash('sha256', 'chave-secreta'.'video-123'.$expires);

        $url = $bunny->embedUrl('video-123');

        $this->assertSame(
            "https://iframe.mediadelivery.net/embed/633749/video-123?token={$tokenEsperado}&expires={$expires}",
            $url
        );
    }

    public function test_download_url_usa_cdn_e_token_base64url(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(2_000_000));
        $bunny = new BunnyService(libraryId: '633749', tokenKey: 'chave-secreta', cdnHostname: 'cdn.exemplo.net', ttl: 60);

        $expires = 2_000_000 + 60;
        $caminho = '/video-123/play_720p.mp4';
        $raw = hash('sha256', 'chave-secreta'.$caminho.$expires, true);
        $tokenEsperado = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        $url = $bunny->downloadUrl('video-123');

        $this->assertSame(
            "https://cdn.exemplo.net{$caminho}?token={$tokenEsperado}&expires={$expires}",
            $url
        );
    }
}
