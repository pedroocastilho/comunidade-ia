<?php

namespace App\Filament\Resources\JornadaTemplates\Pages;

use App\Filament\Resources\JornadaTemplates\JornadaTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJornadaTemplates extends ListRecords
{
    protected static string $resource = JornadaTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
