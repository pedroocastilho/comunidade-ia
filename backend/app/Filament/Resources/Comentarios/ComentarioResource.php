<?php

namespace App\Filament\Resources\Comentarios;

use App\Filament\Resources\Comentarios\Pages\CreateComentario;
use App\Filament\Resources\Comentarios\Pages\EditComentario;
use App\Filament\Resources\Comentarios\Pages\ListComentarios;
use App\Filament\Resources\Comentarios\Schemas\ComentarioForm;
use App\Filament\Resources\Comentarios\Tables\ComentariosTable;
use App\Models\Comentario;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ComentarioResource extends Resource
{
    protected static ?string $model = Comentario::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

    protected static string | \UnitEnum | null $navigationGroup = 'Comunidade';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'comentário';

    protected static ?string $pluralModelLabel = 'Comentários';

    public static function form(Schema $schema): Schema
    {
        return ComentarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComentariosTable::configure($table);
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
            'index' => ListComentarios::route('/'),
            'create' => CreateComentario::route('/create'),
            'edit' => EditComentario::route('/{record}/edit'),
        ];
    }
}
