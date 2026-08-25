<?php

namespace App\Filament\Resources\IaConfiguracaos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class IaConfiguracaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('chave')
                    ->required(),
                Textarea::make('valor')
                    ->columnSpanFull(),
            ]);
    }
}
