<?php

namespace App\Filament\Resources\IaConfiguracaos\Pages;

use App\Filament\Resources\IaConfiguracaos\IaConfiguracaoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIaConfiguracao extends EditRecord
{
    protected static string $resource = IaConfiguracaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
