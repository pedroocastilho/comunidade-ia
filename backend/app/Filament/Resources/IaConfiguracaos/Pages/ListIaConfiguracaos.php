<?php

namespace App\Filament\Resources\IaConfiguracaos\Pages;

use App\Filament\Resources\IaConfiguracaos\IaConfiguracaoResource;
use Filament\Resources\Pages\ListRecords;

class ListIaConfiguracaos extends ListRecords
{
    protected static string $resource = IaConfiguracaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
