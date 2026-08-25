<?php

namespace App\Filament\Resources\Conquistas\Pages;

use App\Filament\Resources\Conquistas\ConquistaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditConquista extends EditRecord
{
    protected static string $resource = ConquistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
