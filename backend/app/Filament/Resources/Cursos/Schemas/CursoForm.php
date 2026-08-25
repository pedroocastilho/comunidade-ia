<?php

namespace App\Filament\Resources\Cursos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CursoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('categoria_id')
                    ->required()
                    ->numeric(),
                TextInput::make('instrutor_id')
                    ->numeric(),
                TextInput::make('titulo')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('descricao')
                    ->columnSpanFull(),
                TextInput::make('capa_url')
                    ->url(),
                TextInput::make('banner_url')
                    ->url(),
                TextInput::make('duracao_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('rascunho'),
                Toggle::make('destaque')
                    ->required(),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('premium')
                    ->label('Premium (venda avulsa)'),
                TextInput::make('produto_externo_id')
                    ->label('ID do produto no checkout'),
                TextInput::make('checkout_url')
                    ->label('URL do checkout')
                    ->url(),
            ]);
    }
}
