<?php

namespace App\Filament\Resources\Instrutors\Pages;

use App\Filament\Resources\Instrutors\InstrutorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstrutors extends ListRecords
{
    protected static string $resource = InstrutorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
