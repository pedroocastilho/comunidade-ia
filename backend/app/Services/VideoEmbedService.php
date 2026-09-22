<?php

namespace App\Services;

use App\Models\Aula;

/**
 * Resolve o video de uma aula para o player.
 * Prioridade: Bunny Stream (bunny_video_id, URL assinada) e depois
 * video_url — YouTube nao listado, Vimeo ou arquivo MP4 direto.
 */
class VideoEmbedService
{
    public function __construct(private BunnyService $bunny) {}

    /**
     * @return array{embed_url: ?string, video_file_url: ?string}
     */
    public function resolver(Aula $aula): array
    {
        if ($aula->bunny_video_id) {
            return [
                'embed_url' => $this->bunny->embedUrl($aula->bunny_video_id),
                'video_file_url' => null,
            ];
        }

        return $this->daUrl($aula->video_url);
    }

    /**
     * @return array{embed_url: ?string, video_file_url: ?string}
     */
    private function daUrl(?string $url): array
    {
        $nada = ['embed_url' => null, 'video_file_url' => null];

        if (! $url || ! str_starts_with($url, 'https://')) {
            return $nada;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?.*?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]{6,20})~', $url, $m)) {
            return [
                'embed_url' => "https://www.youtube-nocookie.com/embed/{$m[1]}?rel=0&modestbranding=1",
                'video_file_url' => null,
            ];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return [
                'embed_url' => "https://player.vimeo.com/video/{$m[1]}",
                'video_file_url' => null,
            ];
        }

        // Qualquer outra URL https e tratada como arquivo de video (MP4 no <video> nativo)
        return ['embed_url' => null, 'video_file_url' => $url];
    }
}
