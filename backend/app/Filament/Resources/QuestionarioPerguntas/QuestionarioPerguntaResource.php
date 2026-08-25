<?php

namespace App\Filament\Resources\QuestionarioPerguntas;

use App\Filament\Resources\QuestionarioPerguntas\Pages\CreateQuestionarioPergunta;
use App\Filament\Resources\QuestionarioPerguntas\Pages\EditQuestionarioPergunta;
use App\Filament\Resources\QuestionarioPerguntas\Pages\ListQuestionarioPerguntas;
use App\Filament\Resources\QuestionarioPerguntas\Schemas\QuestionarioPerguntaForm;
use App\Filament\Resources\QuestionarioPerguntas\Tables\QuestionarioPerguntasTable;
use App\Models\QuestionarioPergunta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionarioPerguntaResource extends Resource
{
    protected static ?string $model = QuestionarioPergunta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return QuestionarioPerguntaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionarioPerguntasTable::configure($table);
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
            'index' => ListQuestionarioPerguntas::route('/'),
            'create' => CreateQuestionarioPergunta::route('/create'),
            'edit' => EditQuestionarioPergunta::route('/{record}/edit'),
        ];
    }
}
