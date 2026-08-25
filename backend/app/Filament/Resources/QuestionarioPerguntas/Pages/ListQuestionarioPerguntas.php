<?php

namespace App\Filament\Resources\QuestionarioPerguntas\Pages;

use App\Filament\Resources\QuestionarioPerguntas\QuestionarioPerguntaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionarioPerguntas extends ListRecords
{
    protected static string $resource = QuestionarioPerguntaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
