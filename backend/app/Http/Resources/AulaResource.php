<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AulaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'duracao' => $this->duracao,
            'is_bonus' => (bool) $this->is_bonus,
            'material_url' => $this->material_url,
            'video_embed_url' => $this->embed_url ?? null,
            'video_file_url' => $this->video_file_url ?? null,
            'concluida' => (bool) ($this->concluida ?? false),
            'posicao_segundos' => (int) ($this->posicao_segundos ?? 0),
        ];
    }
}
