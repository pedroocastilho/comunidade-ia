<?php

namespace App\Filament\Resources\JornadaTemplates\RelationManagers;

use App\Models\Audio;
use App\Models\Aula;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiasRelationManager extends RelationManager
{
    protected static string $relationship = 'dias';

    protected static ?string $title = 'Dias da jornada';

    public function form(Schema $schema): Schema
    {
        // Campos JSON (equivalentes/variante_curta) sao editados como JSON puro:
        // uso avancado, documentado no PRD secao 6.
        $json = fn ($state) => $state === null ? null : json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $deJson = fn ($state) => blank($state) ? null : json_decode($state, true);

        return $schema
            ->components([
                TextInput::make('dia')
                    ->required()
                    ->numeric(),
                TextInput::make('etapa')
                    ->label('Etapa (pilar)')
                    ->required(),
                Select::make('ritual_audio_id')
                    ->label('Ritual (áudio)')
                    ->options(fn () => Audio::orderBy('titulo')->pluck('titulo', 'id'))
                    ->searchable(),
                Select::make('aula_id')
                    ->label('Aula')
                    ->options(fn () => Aula::orderBy('titulo')->pluck('titulo', 'id'))
                    ->searchable(),
                Textarea::make('acao_texto')
                    ->label('Ação (tarefa)')
                    ->columnSpanFull(),
                Textarea::make('equivalentes')
                    ->label('Equivalentes (JSON: {"ritual": [ids], "aula": [ids], "acao": ["texto"]})')
                    ->formatStateUsing($json)
                    ->dehydrateStateUsing($deJson)
                    ->rule('nullable')
                    ->columnSpanFull(),
                Textarea::make('variante_curta')
                    ->label('Variante curta (JSON: campos que substituem no plano 5-10 min)')
                    ->formatStateUsing($json)
                    ->dehydrateStateUsing($deJson)
                    ->rule('nullable')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dia')
            ->defaultSort('dia')
            ->columns([
                TextColumn::make('dia')->sortable(),
                TextColumn::make('etapa'),
                TextColumn::make('ritualAudio.titulo')->label('Ritual'),
                TextColumn::make('aula.titulo')->label('Aula'),
                TextColumn::make('acao_texto')->label('Ação')->limit(40),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
