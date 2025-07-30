<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource\Pages;
use App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource\RelationManagers;
use App\Models\ContabilidadMedica\LiquidacionDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LiquidacionDetalleResource extends Resource
{
    protected static ?string $model = LiquidacionDetalle::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('liquidacion_id')
                    ->relationship('liquidacion', 'id')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('factura_detalle_id')
                    ->relationship('facturaDetalle', 'id')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('centro_id')
                    ->relationship('centro', 'nombre_centro')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('liquidacion.id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('facturaDetalle.id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('centro.nombre_centro')
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
                Tables\Filters\SelectFilter::make('centro')
                    ->relationship('centro', 'nombre_centro'),
                Tables\Filters\Filter::make('monto_honorario')
                    ->form([
                        Forms\Components\TextInput::make('monto_desde')
                            ->numeric()
                            ->label('Monto desde'),
                        Forms\Components\TextInput::make('monto_hasta')
                            ->numeric()
                            ->label('Monto hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['monto_desde'],
                                fn (Builder $query, $amount): Builder => $query->where('monto_honorario', '>=', $amount),
                            )
                            ->when(
                                $data['monto_hasta'],
                                fn (Builder $query, $amount): Builder => $query->where('monto_honorario', '<=', $amount),
                            );
                    })
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
            'index' => Pages\ListLiquidacionDetalles::route('/'),
            'create' => Pages\CreateLiquidacionDetalle::route('/create'),
            'view' => Pages\ViewLiquidacionDetalle::route('/{record}'),
            'edit' => Pages\EditLiquidacionDetalle::route('/{record}/edit'),
        ];
    }
}
