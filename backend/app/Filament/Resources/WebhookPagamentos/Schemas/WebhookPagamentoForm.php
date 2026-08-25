<?php

namespace App\Filament\Resources\WebhookPagamentos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WebhookPagamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plataforma')
                    ->required(),
                TextInput::make('evento')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                Textarea::make('payload')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('processado')
                    ->required(),
                Textarea::make('erro')
                    ->columnSpanFull(),
            ]);
    }
}
