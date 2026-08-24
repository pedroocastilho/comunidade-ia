<?php

namespace App\Filament\Resources\Comentarios\Pages;

use App\Filament\Resources\Comentarios\ComentarioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComentario extends EditRecord
{
    protected static string $resource = ComentarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
