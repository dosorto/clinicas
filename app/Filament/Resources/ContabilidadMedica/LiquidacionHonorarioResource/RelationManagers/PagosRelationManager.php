<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PagosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha_pago')
                    ->required(),
                Forms\Components\TextInput::make('monto')
                    ->required()
                    ->numeric()
                    ->prefix('L')
                    ->minValue(0),
                Forms\Components\Select::make('tipo_pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'cheque' => 'Cheque'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('referencia')
                    ->maxLength(255),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha_pago')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto')
                    ->money('HNL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_pago')
                    ->badge(),
                Tables\Columns\TextColumn::make('referencia')
                    ->searchable(),
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
