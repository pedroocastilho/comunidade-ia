<?php

namespace App\Filament\Resources\Conquistas\Pages;

use App\Filament\Resources\Conquistas\ConquistaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConquistas extends ListRecords
{
    protected static string $resource = ConquistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
