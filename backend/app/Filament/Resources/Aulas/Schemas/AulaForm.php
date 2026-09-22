<?php

namespace App\Filament\Resources\Aulas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AulaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('modulo_id')
                    ->required()
                    ->numeric(),
                TextInput::make('titulo')
                    ->required(),
                Textarea::make('descricao')
                    ->columnSpanFull(),
                TextInput::make('video_url')
                    ->label('Link do vídeo')
                    ->url()
                    ->helperText('YouTube (não listado), Vimeo ou arquivo MP4. Se preencher o Bunny abaixo, o Bunny tem prioridade.')
                    ->columnSpanFull(),
                TextInput::make('bunny_library_id'),
                TextInput::make('bunny_video_id'),
                TextInput::make('duracao')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('material_url')
                    ->url(),
                TextInput::make('ordem')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_bonus')
                    ->required(),
                DateTimePicker::make('liberada_em'),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
