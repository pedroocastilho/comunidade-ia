<?php

namespace App\Filament\Resources\JornadaTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JornadaTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('dimensao_id')
                    ->label('Dimensão (objetivo)')
                    ->relationship('dimensao', 'nome')
                    ->required(),
                TextInput::make('titulo')
                    ->required(),
                Textarea::make('descricao')
                    ->columnSpanFull(),
                TextInput::make('duracao_dias')
                    ->required()
                    ->numeric()
                    ->default(30),
                Select::make('status')
                    ->options(['rascunho' => 'Rascunho', 'publicado' => 'Publicado'])
                    ->required()
                    ->default('rascunho'),
            ]);
    }
}
