<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('factura_detalle_id')
                    ->relationship('facturaDetalle', 'id')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('porcentaje_honorario')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\TextInput::make('monto_honorario')
                    ->required()
                    ->numeric()
                    ->prefix('L')
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('facturaDetalle.id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('porcentaje_honorario')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_honorario')
                    ->money('HNL')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
