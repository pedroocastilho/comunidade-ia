<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvisoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'corpo' => $this->corpo,
            'imagem_url' => $this->imagem_url,
            'publicado_em' => $this->publicado_em,
        ];
    }
}
