<?php

namespace App\Filament\Resources\Audio\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AudioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tipo')
                    ->options(['frequencia' => 'Frequência', 'meditacao' => 'Meditação', 'ritual' => 'Ritual'])
                    ->required(),
                TextInput::make('titulo')
                    ->required(),
                Textarea::make('descricao')
                    ->columnSpanFull(),
                TextInput::make('arquivo_url')
                    ->label('URL do arquivo (MP3)')
                    ->url(),
                TextInput::make('bunny_library_id'),
                TextInput::make('bunny_video_id'),
                TextInput::make('capa_url')
                    ->url(),
                TextInput::make('duracao')
                    ->label('Duração (segundos)')
                    ->required()
                    ->numeric()
                    ->default(0),
                TagsInput::make('tags')
                    ->columnSpanFull(),
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(['rascunho' => 'Rascunho', 'publicado' => 'Publicado'])
                    ->required()
                    ->default('rascunho'),
                Toggle::make('premium'),
                TextInput::make('produto_externo_id'),
            ]);
    }
}
