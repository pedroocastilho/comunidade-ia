<?php

namespace Tests\Unit;

use App\Models\Aula;
use App\Services\BunnyService;
use App\Services\VideoEmbedService;
use PHPUnit\Framework\TestCase;

class VideoEmbedServiceTest extends TestCase
{
    private VideoEmbedService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VideoEmbedService(new BunnyService('123', 'chave-teste', 'cdn.teste.net', 100));
    }

    private function aula(array $atributos): Aula
    {
        return new Aula($atributos);
    }

    public function test_bunny_tem_prioridade_sobre_video_url(): void
    {
        $video = $this->service->resolver($this->aula([
            'bunny_video_id' => 'abc-123',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]));

        $this->assertStringStartsWith('https://iframe.mediadelivery.net/embed/123/abc-123', $video['embed_url']);
        $this->assertNull($video['video_file_url']);
    }

    public function test_youtube_watch_vira_embed_nocookie(): void
    {
        $video = $this->service->resolver($this->aula(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s']));

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&modestbranding=1', $video['embed_url']);
    }

    public function test_youtu_be_curto_vira_embed(): void
    {
        $video = $this->service->resolver($this->aula(['video_url' => 'https://youtu.be/dQw4w9WgXcQ']));

        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&modestbranding=1', $video['embed_url']);
    }

    public function test_vimeo_vira_embed_do_player(): void
    {
        $video = $this->service->resolver($this->aula(['video_url' => 'https://vimeo.com/123456789']));

        $this->assertSame('https://player.vimeo.com/video/123456789', $video['embed_url']);
    }

    public function test_mp4_direto_vai_para_video_file_url(): void
    {
        $video = $this->service->resolver($this->aula(['video_url' => 'https://cdn.exemplo.com/aula.mp4']));

        $this->assertNull($video['embed_url']);
        $this->assertSame('https://cdn.exemplo.com/aula.mp4', $video['video_file_url']);
    }

    public function test_url_sem_https_e_ignorada(): void
    {
        $video = $this->service->resolver($this->aula(['video_url' => 'http://inseguro.com/aula.mp4']));

        $this->assertNull($video['embed_url']);
        $this->assertNull($video['video_file_url']);
    }

    public function test_aula_sem_video_retorna_nulos(): void
    {
        $video = $this->service->resolver($this->aula([]));

        $this->assertNull($video['embed_url']);
        $this->assertNull($video['video_file_url']);
    }
}
