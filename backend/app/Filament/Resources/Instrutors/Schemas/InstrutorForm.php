<?php

namespace App\Filament\Resources\Instrutors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InstrutorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('foto_url')
                    ->url(),
            ]);
    }
}
