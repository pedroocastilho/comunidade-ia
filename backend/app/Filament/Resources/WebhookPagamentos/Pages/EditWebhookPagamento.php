<?php

namespace App\Filament\Resources\WebhookPagamentos\Pages;

use App\Filament\Resources\WebhookPagamentos\WebhookPagamentoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebhookPagamento extends EditRecord
{
    protected static string $resource = WebhookPagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
