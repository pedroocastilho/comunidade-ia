<?php

namespace App\Filament\Resources\Denuncias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DenunciaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('denunciavel_type')
                    ->required(),
                TextInput::make('denunciavel_id')
                    ->required()
                    ->numeric(),
                TextInput::make('motivo'),
                Toggle::make('resolvida')
                    ->required(),
            ]);
    }
}
