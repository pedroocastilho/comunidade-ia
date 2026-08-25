<?php

namespace App\Filament\Resources\WebhookPagamentos;

use App\Filament\Resources\WebhookPagamentos\Pages\CreateWebhookPagamento;
use App\Filament\Resources\WebhookPagamentos\Pages\EditWebhookPagamento;
use App\Filament\Resources\WebhookPagamentos\Pages\ListWebhookPagamentos;
use App\Filament\Resources\WebhookPagamentos\Schemas\WebhookPagamentoForm;
use App\Filament\Resources\WebhookPagamentos\Tables\WebhookPagamentosTable;
use App\Models\WebhookPagamento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WebhookPagamentoResource extends Resource
{
    protected static ?string $label = 'Webhook de pagamento';

    protected static ?string $pluralLabel = 'Webhooks de pagamento';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    protected static ?string $model = WebhookPagamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WebhookPagamentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebhookPagamentosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhookPagamentos::route('/'),
        ];
    }
}
