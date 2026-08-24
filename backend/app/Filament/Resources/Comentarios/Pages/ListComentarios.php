<?php

namespace App\Filament\Resources\Comentarios\Pages;

use App\Filament\Resources\Comentarios\ComentarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComentarios extends ListRecords
{
    protected static string $resource = ComentarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
