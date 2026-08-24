<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CursoDetalhadoResource extends JsonResource
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
            'views' => $this->views,
            'progresso_percentual' => (int) ($this->progresso_percentual ?? 0),
            'categoria' => $this->whenLoaded('categoria', fn () => [
                'id' => $this->categoria->id,
                'nome' => $this->categoria->nome,
                'slug' => $this->categoria->slug,
            ]),
            'instrutor' => $this->whenLoaded('instrutor', fn () => $this->instrutor ? [
                'id' => $this->instrutor->id,
                'nome' => $this->instrutor->nome,
                'bio' => $this->instrutor->bio,
                'foto_url' => $this->instrutor->foto_url,
            ] : null),
            'modulos' => $this->whenLoaded('modulos', fn () => $this->modulos->map(fn ($m) => [
                'id' => $m->id,
                'titulo' => $m->titulo,
                'ordem' => $m->ordem,
                'aulas' => $m->aulas->map(fn ($a) => [
                    'id' => $a->id,
                    'titulo' => $a->titulo,
                    'duracao' => $a->duracao,
                    'is_bonus' => (bool) $a->is_bonus,
                    'concluida' => (bool) ($a->concluida ?? false),
                    'posicao_segundos' => (int) ($a->posicao_segundos ?? 0),
                ]),
            ])),
        ];
    }
}
