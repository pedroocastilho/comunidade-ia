<?php

namespace App\Filament\Resources\Instrutors\Pages;

use App\Filament\Resources\Instrutors\InstrutorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstrutor extends EditRecord
{
    protected static string $resource = InstrutorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
