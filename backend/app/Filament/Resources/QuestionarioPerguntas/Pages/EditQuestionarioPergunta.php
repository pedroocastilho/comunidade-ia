<?php

namespace App\Filament\Resources\QuestionarioPerguntas\Pages;

use App\Filament\Resources\QuestionarioPerguntas\QuestionarioPerguntaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionarioPergunta extends EditRecord
{
    protected static string $resource = QuestionarioPerguntaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
