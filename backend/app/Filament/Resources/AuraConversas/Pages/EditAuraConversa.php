<?php

namespace App\Filament\Resources\AuraConversas\Pages;

use App\Filament\Resources\AuraConversas\AuraConversaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuraConversa extends EditRecord
{
    protected static string $resource = AuraConversaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
