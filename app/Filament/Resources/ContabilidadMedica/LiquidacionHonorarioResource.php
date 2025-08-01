<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages;
use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\RelationManagers;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LiquidacionHonorarioResource extends Resource
{
    protected static ?string $model = LiquidacionHonorario::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false; // Ocultar - muy complejo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico.persona', 'primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_completo)
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('centro_id')
                    ->relationship('centro', 'nombre_centro')
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('periodo_inicio')
                    ->required(),
                Forms\Components\DatePicker::make('periodo_fin')
                    ->required(),
                Forms\Components\TextInput::make('monto_total')
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
                Forms\Components\Select::make('tipo_liquidacion')
                    ->options([
                        'servicios' => 'Servicios',
                        'honorarios' => 'Honorarios',
                        'mixto' => 'Mixto'
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('periodo_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periodo_fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_total')
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
                Tables\Columns\TextColumn::make('tipo_liquidacion')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial' => 'Pago Parcial',
                        'pagado' => 'Pagado',
                        'anulado' => 'Anulado'
                    ]),
                Tables\Filters\SelectFilter::make('tipo_liquidacion')
                    ->options([
                        'servicios' => 'Servicios',
                        'honorarios' => 'Honorarios',
                        'mixto' => 'Mixto'
                    ]),
                Tables\Filters\SelectFilter::make('medico')
                    ->relationship('medico.persona', 'primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_completo),
                Tables\Filters\SelectFilter::make('centro')
                    ->relationship('centro', 'nombre_centro'),
                Tables\Filters\Filter::make('periodo')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periodo_inicio', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periodo_fin', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('monto_total')
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
                                fn (Builder $query, $amount): Builder => $query->where('monto_total', '>=', $amount),
                            )
                            ->when(
                                $data['monto_hasta'],
                                fn (Builder $query, $amount): Builder => $query->where('monto_total', '<=', $amount),
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
            RelationManagers\DetallesRelationManager::class,
            RelationManagers\PagosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiquidacionHonorarios::route('/'),
            'create' => Pages\CreateLiquidacionHonorario::route('/create'),
            'view' => Pages\ViewLiquidacionHonorario::route('/{record}'),
            'edit' => Pages\EditLiquidacionHonorario::route('/{record}/edit'),
        ];
    }
}
