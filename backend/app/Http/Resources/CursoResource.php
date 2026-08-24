<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CursoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'capa_url' => $this->capa_url,
            'banner_url' => $this->banner_url,
            'duracao_total' => $this->duracao_total,
            'destaque' => (bool) $this->destaque,
            'views' => $this->views,
            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nome' => $this->categoria->nome,
                'slug' => $this->categoria->slug,
            ]),
            'instrutor' => $this->whenLoaded('instrutor', fn () => $this->instrutor ? [
                'id' => $this->instrutor->id,
                'nome' => $this->instrutor->nome,
            ] : null),
        ];
    }
}
