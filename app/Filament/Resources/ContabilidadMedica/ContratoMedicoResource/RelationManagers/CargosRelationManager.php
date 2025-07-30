<?php

namespace App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CargosRelationManager extends RelationManager
{
    protected static string $relationship = 'cargos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('descripcion')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('periodo_inicio')
                    ->required(),
                Forms\Components\DatePicker::make('periodo_fin')
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                Forms\Components\TextInput::make('impuesto_total')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial' => 'Pago Parcial',
                        'pagado' => 'Pagado',
                        'anulado' => 'Anulado'
                    ])
                    ->required(),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('periodo_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periodo_fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('HNL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'parcial' => 'info',
                        'pagado' => 'success',
                        'anulado' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial' => 'Pago Parcial',
                        'pagado' => 'Pagado',
                        'anulado' => 'Anulado'
                    ]),
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
