<?php

namespace App\Filament\Resources\Comentarios\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ComentarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('aula_id')
                    ->required()
                    ->numeric(),
                Textarea::make('texto')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('comentario_pai_id')
                    ->numeric(),
                Toggle::make('aprovado')
                    ->required(),
            ]);
    }
}
