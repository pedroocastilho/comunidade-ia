<?php

namespace App\Filament\Resources\IaConfiguracaos;

use App\Filament\Resources\IaConfiguracaos\Pages\CreateIaConfiguracao;
use App\Filament\Resources\IaConfiguracaos\Pages\EditIaConfiguracao;
use App\Filament\Resources\IaConfiguracaos\Pages\ListIaConfiguracaos;
use App\Filament\Resources\IaConfiguracaos\Schemas\IaConfiguracaoForm;
use App\Filament\Resources\IaConfiguracaos\Tables\IaConfiguracaosTable;
use App\Models\IaConfiguracao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IaConfiguracaoResource extends Resource
{
    protected static ?string $label = 'Configuração da IA';

    protected static ?string $pluralLabel = 'Configurações da IA';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    protected static ?string $model = IaConfiguracao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string | \UnitEnum | null $navigationGroup = 'Aura';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return IaConfiguracaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IaConfiguracaosTable::configure($table);
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
            'index' => ListIaConfiguracaos::route('/'),
            'edit' => EditIaConfiguracao::route('/{record}/edit'),
        ];
    }
}
