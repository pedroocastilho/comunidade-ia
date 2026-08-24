<?php

namespace App\Filament\Resources\Avisos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AvisoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->required(),
                Textarea::make('corpo')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('imagem_url')
                    ->url(),
                TextInput::make('autor_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('publicado_em'),
            ]);
    }
}
