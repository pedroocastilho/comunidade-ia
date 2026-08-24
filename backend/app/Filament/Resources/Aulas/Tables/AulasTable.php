<?php

namespace App\Filament\Resources\Aulas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AulasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('modulo_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('bunny_library_id')
                    ->searchable(),
                TextColumn::make('bunny_video_id')
                    ->searchable(),
                TextColumn::make('duracao')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('material_url')
                    ->searchable(),
                TextColumn::make('ordem')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_bonus')
                    ->boolean(),
                TextColumn::make('liberada_em')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
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
