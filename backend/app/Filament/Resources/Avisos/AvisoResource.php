<?php

namespace App\Filament\Resources\Avisos;

use App\Filament\Resources\Avisos\Pages\CreateAviso;
use App\Filament\Resources\Avisos\Pages\EditAviso;
use App\Filament\Resources\Avisos\Pages\ListAvisos;
use App\Filament\Resources\Avisos\Schemas\AvisoForm;
use App\Filament\Resources\Avisos\Tables\AvisosTable;
use App\Models\Aviso;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AvisoResource extends Resource
{
    protected static ?string $model = Aviso::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AvisoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvisosTable::configure($table);
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
            'index' => ListAvisos::route('/'),
            'create' => CreateAviso::route('/create'),
            'edit' => EditAviso::route('/{record}/edit'),
        ];
    }
}
