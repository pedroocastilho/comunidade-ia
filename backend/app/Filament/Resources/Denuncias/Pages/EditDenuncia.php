<?php

namespace App\Filament\Resources\Denuncias\Pages;

use App\Filament\Resources\Denuncias\DenunciaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDenuncia extends EditRecord
{
    protected static string $resource = DenunciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
