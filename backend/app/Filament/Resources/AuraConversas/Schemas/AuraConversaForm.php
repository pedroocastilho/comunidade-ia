<?php

namespace App\Filament\Resources\AuraConversas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuraConversaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('titulo'),
                TextInput::make('classificacao'),
            ]);
    }
}
