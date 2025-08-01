<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;
use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\RelationManagers;
use App\Models\ContabilidadMedica\PagoHonorario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagoHonorarioResource extends Resource
{
    protected static ?string $model = PagoHonorario::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 5;
    protected static ?string $modelLabel = 'Pago de Honorario';
    protected static ?string $pluralModelLabel = 'Pagos de Honorarios';
    protected static bool $shouldRegisterNavigation = false; // Ocultar - muy complejo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('liquidacion_id')
                    ->relationship('liquidacion', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "Liquidación #{$record->id} - {$record->medico->persona->nombre_completo} - L.{$record->monto_total}")
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

                Forms\Components\TextInput::make('referencia_bancaria')
                    ->maxLength(255)
                    ->placeholder('Número de cheque, referencia de transferencia, etc.'),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('retencion_isr_pct')
                            ->label('Retención ISR %')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('retencion_isr_monto')
                            ->label('Monto Retención ISR')
                            ->numeric()
                            ->prefix('L')
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('liquidacion.medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('liquidacion.monto_total')
                    ->label('Total de Liquidación')
                    ->money('HNL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_pago')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto_pagado')
                    ->money('HNL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('retencion_isr_pct')
                    ->label('ISR %')
                    ->suffix('%')
                    ->numeric(2),

                Tables\Columns\TextColumn::make('retencion_isr_monto')
                    ->label('Monto ISR')
                    ->money('HNL'),

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

                Tables\Columns\TextColumn::make('referencia_bancaria')
                    ->searchable()
                    ->label('Referencia'),

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

                Tables\Filters\Filter::make('monto_retencion')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->numeric()
                            ->label('Monto ISR mínimo'),
                        Forms\Components\TextInput::make('max')
                            ->numeric()
                            ->label('Monto ISR máximo'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn (Builder $query, $min): Builder => $query->where('retencion_isr_monto', '>=', $min),
                            )
                            ->when(
                                $data['max'],
                                fn (Builder $query, $max): Builder => $query->where('retencion_isr_monto', '<=', $max),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generarRecibo')
                    ->label('Generar Recibo')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn (PagoHonorario $record): string => route('filament.admin.resources.contabilidad-medica.pago-honorarios.generar-recibo', ['pagoId' => $record->id]))
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('completarLiquidacion')
                    ->label('Completar Liquidación')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(function (PagoHonorario $record): bool {
                        // Solo mostrar si la liquidación está en estado parcial
                        $liquidacion = $record->liquidacion;
                        return $liquidacion && $liquidacion->estado === 'parcial';
                    })
                    ->url(function (PagoHonorario $record): string {
                        // Redireccionar a la página de crear pago con la liquidación preseleccionada
                        return route('filament.admin.resources.contabilidad-medica.pago-honorarios.create', [
                            'liquidacion_id' => $record->liquidacion_id
                        ]);
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('pagoRapido')
                    ->label('Pago Rápido')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->action(function () {
                        // Redireccionar a la página de crear pago
                        return redirect()->route('filament.admin.resources.contabilidad-medica.pago-honorarios.create');
                    }),
                    
                Tables\Actions\Action::make('liquidacionesPendientes')
                    ->label('Ver Liquidaciones Pendientes')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('warning')
                    ->url(fn (): string => route('filament.admin.resources.contabilidad-medica.liquidacion-honorarios.index', [
                        'tableFilters[estado][value]' => 'pendiente'
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('generarRecibosMultiples')
                        ->label('Generar Recibos')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->action(function (array $records) {
                            // Lógica para generar recibos múltiples
                            // Aquí podrías generar un ZIP con todos los recibos
                        }),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        // Mostrar cantidad de liquidaciones pendientes como distintivo en el menú
        return \App\Models\ContabilidadMedica\LiquidacionHonorario::whereIn('estado', ['pendiente', 'parcial'])->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        // Color rojo si hay más de 5 pendientes, amarillo si hay al menos 1
        $pendientes = \App\Models\ContabilidadMedica\LiquidacionHonorario::whereIn('estado', ['pendiente', 'parcial'])->count();
        
        if ($pendientes > 5) {
            return 'danger';
        }
        
        if ($pendientes >= 1) {
            return 'warning';
        }
        
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagoHonorarios::route('/'),
            'create' => Pages\CreatePagoHonorarioSimple::route('/create'),
            'view' => Pages\ViewPagoHonorario::route('/{record}'),
            'edit' => Pages\EditPagoHonorario::route('/{record}/edit'),
            'generate-receipt' => Pages\GenerarRecibo::route('/{record}/generar-recibo'),
        ];
    }
}
