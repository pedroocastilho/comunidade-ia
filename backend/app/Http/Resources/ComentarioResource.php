<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComentarioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'texto' => $this->texto,
            'comentario_pai_id' => $this->comentario_pai_id,
            'created_at' => $this->created_at,
            'autor' => [
                'id' => $this->user->id,
                'nome' => $this->user->name,
            ],
        ];
    }
}
