<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource\Pages;
use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource\RelationManagers;
use App\Models\ContabilidadMedica\PagoCargoMedico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagoCargoMedicoResource extends Resource
{
    protected static ?string $model = PagoCargoMedico::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Pago de Cargo Médico';
    protected static ?string $pluralModelLabel = 'Pagos de Cargos Médicos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cargo_id')
                    ->relationship('cargo', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "Cargo #{$record->id} - {$record->medico->persona->nombre_completo} - L.{$record->total}")
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('centro_id')
                    ->relationship('centro', 'nombre_centro')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\DatePicker::make('fecha_pago')
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('monto_pagado')
                    ->required()
                    ->numeric()
                    ->prefix('L'),

                Forms\Components\Select::make('metodo_pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'cheque' => 'Cheque',
                        'tarjeta' => 'Tarjeta',
                        'otro' => 'Otro'
                    ])
                    ->required(),

                Forms\Components\TextInput::make('referencia')
                    ->maxLength(255)
                    ->placeholder('Número de cheque, referencia de transferencia, etc.'),

                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cargo.medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cargo.total')
                    ->label('Total del Cargo')
                    ->money('HNL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_pago')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_pagado')
                    ->money('HNL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('metodo_pago')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'efectivo' => 'success',
                        'transferencia' => 'info',
                        'cheque' => 'warning',
                        'tarjeta' => 'primary',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('referencia')
                    ->searchable(),

                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metodo_pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'cheque' => 'Cheque',
                        'tarjeta' => 'Tarjeta',
                        'otro' => 'Otro'
                    ]),

                Tables\Filters\SelectFilter::make('centro')
                    ->relationship('centro', 'nombre_centro'),

                Tables\Filters\Filter::make('fecha_pago')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_pago', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagoCargoMedicos::route('/'),
            'create' => Pages\CreatePagoCargoMedico::route('/create'),
            'view' => Pages\ViewPagoCargoMedico::route('/{record}'),
            'edit' => Pages\EditPagoCargoMedico::route('/{record}/edit'),
        ];
    }
}
