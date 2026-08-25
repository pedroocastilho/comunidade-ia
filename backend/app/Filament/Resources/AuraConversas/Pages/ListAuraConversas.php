<?php

namespace App\Filament\Resources\AuraConversas\Pages;

use App\Filament\Resources\AuraConversas\AuraConversaResource;
use Filament\Resources\Pages\ListRecords;

class ListAuraConversas extends ListRecords
{
    protected static string $resource = AuraConversaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
