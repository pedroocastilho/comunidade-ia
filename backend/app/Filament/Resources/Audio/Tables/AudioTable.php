<?php

namespace App\Filament\Resources\Audio\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AudioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo')
                    ->searchable(),
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('arquivo_url')
                    ->searchable(),
                TextColumn::make('bunny_library_id')
                    ->searchable(),
                TextColumn::make('bunny_video_id')
                    ->searchable(),
                TextColumn::make('capa_url')
                    ->searchable(),
                TextColumn::make('duracao')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ordem')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                IconColumn::make('premium')
                    ->boolean(),
                TextColumn::make('produto_externo_id')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
