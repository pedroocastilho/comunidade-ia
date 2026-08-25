<?php

namespace App\Filament\Resources\Conquistas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConquistaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->required(),
                TextInput::make('nome')
                    ->required(),
                TextInput::make('descricao')
                    ->required(),
                TextInput::make('icone')
                    ->required()
                    ->default('✦'),
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
