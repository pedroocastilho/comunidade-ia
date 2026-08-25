<?php

namespace App\Filament\Resources\Conquistas;

use App\Filament\Resources\Conquistas\Pages\CreateConquista;
use App\Filament\Resources\Conquistas\Pages\EditConquista;
use App\Filament\Resources\Conquistas\Pages\ListConquistas;
use App\Filament\Resources\Conquistas\Schemas\ConquistaForm;
use App\Filament\Resources\Conquistas\Tables\ConquistasTable;
use App\Models\Conquista;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ConquistaResource extends Resource
{
    protected static ?string $modelLabel = 'conquista';

    protected static ?string $pluralModelLabel = 'Conquistas';

    protected static string | \UnitEnum | null $navigationGroup = 'Gestão';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    protected static ?string $model = Conquista::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    public static function form(Schema $schema): Schema
    {
        return ConquistaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConquistasTable::configure($table);
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
            'index' => ListConquistas::route('/'),
            'edit' => EditConquista::route('/{record}/edit'),
        ];
    }
}
