<?php

namespace App\Filament\Resources\WebhookPagamentos\Pages;

use App\Filament\Resources\WebhookPagamentos\WebhookPagamentoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebhookPagamentos extends ListRecords
{
    protected static string $resource = WebhookPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
