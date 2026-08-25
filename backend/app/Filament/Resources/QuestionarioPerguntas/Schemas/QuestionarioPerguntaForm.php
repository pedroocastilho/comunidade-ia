<?php

namespace App\Filament\Resources\QuestionarioPerguntas\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionarioPerguntaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->helperText('A pontuação do Aura Score é fixa em código e depende da ordem (1-12). Não mude a ordem sem alinhar com o dev.'),
                Select::make('tipo')
                    ->options(['texto' => 'Texto', 'escala' => 'Escala 0-10', 'escolha_unica' => 'Escolha única'])
                    ->required(),
                Textarea::make('texto')
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('opcoes')
                    ->label('Opções (para escolha única)')
                    ->schema([
                        TextInput::make('valor')->required(),
                        TextInput::make('rotulo')->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Toggle::make('obrigatoria'),
                Select::make('dimensao_id')
                    ->label('Dimensão')
                    ->relationship('dimensao', 'nome'),
                Toggle::make('alimenta_memoria')
                    ->label('Alimenta a memória da Aura'),
            ]);
    }
}
