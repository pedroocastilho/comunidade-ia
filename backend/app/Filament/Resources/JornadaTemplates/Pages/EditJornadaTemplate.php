<?php

namespace App\Filament\Resources\JornadaTemplates\Pages;

use App\Filament\Resources\JornadaTemplates\JornadaTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJornadaTemplate extends EditRecord
{
    protected static string $resource = JornadaTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
