<?php

namespace App\Filament\Resources\Instrutors;

use App\Filament\Resources\Instrutors\Pages\CreateInstrutor;
use App\Filament\Resources\Instrutors\Pages\EditInstrutor;
use App\Filament\Resources\Instrutors\Pages\ListInstrutors;
use App\Filament\Resources\Instrutors\Schemas\InstrutorForm;
use App\Filament\Resources\Instrutors\Tables\InstrutorsTable;
use App\Models\Instrutor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InstrutorResource extends Resource
{
    protected static ?string $model = Instrutor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InstrutorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstrutorsTable::configure($table);
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
            'index' => ListInstrutors::route('/'),
            'create' => CreateInstrutor::route('/create'),
            'edit' => EditInstrutor::route('/{record}/edit'),
        ];
    }
}
