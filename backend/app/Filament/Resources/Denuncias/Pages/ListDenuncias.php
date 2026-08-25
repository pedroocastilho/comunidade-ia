<?php

namespace App\Filament\Resources\Denuncias\Pages;

use App\Filament\Resources\Denuncias\DenunciaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDenuncias extends ListRecords
{
    protected static string $resource = DenunciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
