<?php

namespace App\Filament\Resources\Denuncias;

use App\Filament\Resources\Denuncias\Pages\CreateDenuncia;
use App\Filament\Resources\Denuncias\Pages\EditDenuncia;
use App\Filament\Resources\Denuncias\Pages\ListDenuncias;
use App\Filament\Resources\Denuncias\Schemas\DenunciaForm;
use App\Filament\Resources\Denuncias\Tables\DenunciasTable;
use App\Models\Denuncia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DenunciaResource extends Resource
{
    protected static ?string $modelLabel = 'denúncia';

    protected static ?string $pluralModelLabel = 'Denúncias';

    protected static string | \UnitEnum | null $navigationGroup = 'Comunidade';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    protected static ?string $model = Denuncia::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    public static function form(Schema $schema): Schema
    {
        return DenunciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DenunciasTable::configure($table);
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
            'index' => ListDenuncias::route('/'),
            'edit' => EditDenuncia::route('/{record}/edit'),
        ];
    }
}
