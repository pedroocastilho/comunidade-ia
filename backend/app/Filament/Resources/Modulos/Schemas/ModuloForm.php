<?php

namespace App\Filament\Resources\Modulos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ModuloForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('curso_id')
                    ->required()
                    ->numeric(),
                TextInput::make('titulo')
                    ->required(),
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
