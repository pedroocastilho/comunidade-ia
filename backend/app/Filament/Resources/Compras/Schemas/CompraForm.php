<?php

namespace App\Filament\Resources\Compras\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('produto_externo_id')
                    ->required(),
                TextInput::make('curso_id')
                    ->numeric(),
                TextInput::make('audio_id')
                    ->numeric(),
                Textarea::make('payload')
                    ->columnSpanFull(),
            ]);
    }
}
