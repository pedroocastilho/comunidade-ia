<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('icone'),
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
