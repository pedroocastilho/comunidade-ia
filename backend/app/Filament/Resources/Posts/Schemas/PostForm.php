<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Textarea::make('corpo')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('fixado')
                    ->required(),
                Select::make('status')
                    ->options(['publicado' => 'Publicado', 'pendente' => 'Pendente (aguardando aprovação)', 'oculto' => 'Oculto (moderado)'])
                    ->required()
                    ->default('publicado'),
            ]);
    }
}
