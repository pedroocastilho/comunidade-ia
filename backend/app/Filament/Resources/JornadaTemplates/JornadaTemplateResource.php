<?php

namespace App\Filament\Resources\JornadaTemplates;

use App\Filament\Resources\JornadaTemplates\Pages\CreateJornadaTemplate;
use App\Filament\Resources\JornadaTemplates\Pages\EditJornadaTemplate;
use App\Filament\Resources\JornadaTemplates\Pages\ListJornadaTemplates;
use App\Filament\Resources\JornadaTemplates\Schemas\JornadaTemplateForm;
use App\Filament\Resources\JornadaTemplates\Tables\JornadaTemplatesTable;
use App\Models\JornadaTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JornadaTemplateResource extends Resource
{
    protected static ?string $model = JornadaTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string | \UnitEnum | null $navigationGroup = 'Jornada';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'template de jornada';

    protected static ?string $pluralModelLabel = 'Templates de Jornada';

    public static function form(Schema $schema): Schema
    {
        return JornadaTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JornadaTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DiasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJornadaTemplates::route('/'),
            'create' => CreateJornadaTemplate::route('/create'),
            'edit' => EditJornadaTemplate::route('/{record}/edit'),
        ];
    }
}
