<?php

namespace App\Filament\Resources\AuraConversas;

use App\Filament\Resources\AuraConversas\Pages\CreateAuraConversa;
use App\Filament\Resources\AuraConversas\Pages\EditAuraConversa;
use App\Filament\Resources\AuraConversas\Pages\ListAuraConversas;
use App\Filament\Resources\AuraConversas\Schemas\AuraConversaForm;
use App\Filament\Resources\AuraConversas\Tables\AuraConversasTable;
use App\Models\AuraConversa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuraConversaResource extends Resource
{
    protected static ?string $label = 'Conversa da Aura';

    protected static ?string $pluralLabel = 'Conversas da Aura';

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

    protected static ?string $model = AuraConversa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AuraConversaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuraConversasTable::configure($table);
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
            'index' => ListAuraConversas::route('/'),
        ];
    }
}
