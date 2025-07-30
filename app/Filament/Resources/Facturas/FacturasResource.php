<?php

namespace App\Filament\Resources\Facturas;

use App\Filament\Resources\Facturas\FacturasResource\Pages;
use App\Filament\Resources\Facturas\FacturasResource\RelationManagers;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacturasResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static ?string $slug = 'facturas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Factura')
                    ->schema([
                        Forms\Components\Hidden::make('consulta_id'),
                        Forms\Components\Hidden::make('paciente_id'),
                        Forms\Components\Hidden::make('medico_id'),
                        
                        Forms\Components\Placeholder::make('consulta_info')
                            ->label('Información de la Consulta')
                            ->content(function (?Factura $record): string {
                                $consultaId = request()->get('consulta_id') ?? $record?->consulta_id;
                                
                                if ($consultaId) {
                                    $consulta = \App\Models\Consulta::with(['paciente.persona', 'medico.persona'])
                                        ->find($consultaId);
                                    
                                    if ($consulta) {
                                        return "**Consulta:** {$consulta->id}\n" .
                                               "**Paciente:** {$consulta->paciente->persona->nombres} {$consulta->paciente->persona->apellidos}\n" .
                                               "**Médico:** {$consulta->medico->persona->nombres} {$consulta->medico->persona->apellidos}\n" .
                                               "**Fecha:** {$consulta->created_at->format('d/m/Y')}";
                                    }
                                }
                                
                                return 'No hay consulta asociada';
                            })
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('usa_cai')
                            ->label('¿Emitir con CAI?')
                            ->default(true)
                            ->helperText('Desactívalo para emitir una pro-forma o recibo interno'),

                        Forms\Components\Section::make('Pago')
                            ->schema([
                                // ─── 2.1 Repeater ──────────────────────────────
                                Repeater::make('pagos')
                                    ->label('Métodos de Pago')
                                    ->schema([
                                        Select::make('tipo_pago_id')
                                            ->label('Tipo de Pago')
                                            ->options(\App\Models\TipoPago::pluck('nombre','id'))
                                            ->required()
                                            ->columnSpan(1),

                                        TextInput::make('monto_recibido')
                                            ->label('Monto Recibido')
                                            ->prefix('L.')
                                            ->numeric()
                                            ->step(0.01)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Calcular totales de todos los pagos
                                                $pagos = $get('../../pagos') ?? [];
                                                $totalPagado = 0;
                                                
                                                foreach ($pagos as $pago) {
                                                    $totalPagado += (float) ($pago['monto_recibido'] ?? 0);
                                                }
                                                
                                                $totalFactura = (float) ($get('../../total') ?? 0);
                                                $cambio = max(0, $totalPagado - $totalFactura);
                                                
                                                $set('../../total_pagado', round($totalPagado, 2));
                                                $set('../../cambio', round($cambio, 2));
                                            })
                                            ->columnSpan(1),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel('Agregar otro método')
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->live(),

                                // ─── 2.2 Resumen dinámico ──────────────────────
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('total_a_pagar_display')
                                            ->label('Total a Pagar')
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn (callable $get) => number_format($get('total') ?? 0, 2))
                                            ->extraAttributes(['class' => 'font-bold']),
                                            
                                        Forms\Components\TextInput::make('total_pagado')
                                            ->label('Total Pagado')
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(0)
                                            ->extraAttributes(['class' => 'font-bold text-green-600']),
                                            
                                        Forms\Components\TextInput::make('cambio')
                                            ->label('Cambio a Devolver')
                                            ->prefix('L.')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->default(0)
                                            ->extraAttributes(['class' => 'font-bold text-blue-600'])
                                            ->helperText('Se calcula automáticamente si el pago excede el total'),
                                    ])
                                    ->columnSpanFull(),

                                // ─── 2.3 Campos ocultos para no interferir ───
                                Forms\Components\Hidden::make('total_pagado_hidden')->dehydrated(false),
                                Forms\Components\Hidden::make('cambio_hidden')->dehydrated(false),

                            ])
                            ->columns(1)
                            ->collapsible(),


                        Forms\Components\DatePicker::make('fecha_emision')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\Select::make('estado')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'PAGADA' => 'Pagada',
                                'ANULADA' => 'Anulada',
                                'PARCIAL' => 'Parcial',
                            ])
                            ->default('PENDIENTE')
                            ->required(),
                            
                        Forms\Components\Textarea::make('observaciones')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (Suma de servicios)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado automáticamente desde los servicios'),
                            
                        Forms\Components\Select::make('descuento_id')
                            ->label('Descuento Aplicado')
                            ->options(function () {
                                return \App\Models\Descuento::where('activo', 'SI')
                                    ->where(function ($query) {
                                        $hoy = now()->toDateString();
                                        $query->where('aplica_desde', '<=', $hoy)
                                              ->where(function ($q) use ($hoy) {
                                                  $q->whereNull('aplica_hasta')
                                                    ->orWhere('aplica_hasta', '>=', $hoy);
                                              });
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($descuento) {
                                        $valor = $descuento->tipo === 'PORCENTAJE' 
                                            ? "{$descuento->valor}%" 
                                            : "L. " . number_format($descuento->valor, 2);
                                        return [$descuento->id => "{$descuento->nombre} ({$valor})"];
                                    });
                            })
                            ->searchable()
                            ->nullable()
                            ->placeholder('Sin descuento')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $subtotal = (float) ($get('subtotal') ?? 0);
                                $impuestoTotal = (float) ($get('impuesto_total') ?? 0);
                                
                                $descuentoTotal = 0;
                                
                                if ($state) {
                                    $descuento = \App\Models\Descuento::find($state);
                                    if ($descuento) {
                                        if ($descuento->tipo === 'PORCENTAJE') {
                                            $descuentoTotal = ($subtotal * $descuento->valor) / 100;
                                        } else { // MONTO
                                            $descuentoTotal = min($descuento->valor, $subtotal);
                                        }
                                    }
                                }
                                
                                $total = $subtotal + $impuestoTotal - $descuentoTotal;
                                
                                $set('descuento_total', round($descuentoTotal, 2));
                                $set('total', round($total, 2));
                            }),
                            
                        Forms\Components\TextInput::make('descuento_total')
                            ->label('Monto del Descuento')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado automáticamente según el descuento seleccionado'),

                        Select::make('impuesto_id')
                            ->label('Impuesto (ISV) global')
                            ->options(
                                \App\Models\Impuesto::pluck('nombre', 'id')
                                    ->map(fn ($n, $id) => $n . ' (' . \App\Models\Impuesto::find($id)->porcentaje . '%)')
                            )
                            ->searchable()
                            ->nullable()
                            ->helperText('Opcional — sobrescribe los impuestos línea-por-línea')
                            ->live()                           //   ↙ recalcula cuando cambia
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $subtotal = (float) $get('subtotal');
                                $descuento = (float) $get('descuento_total');

                                $impuestoPct = $state
                                    ? \App\Models\Impuesto::find($state)?->porcentaje ?? 0
                                    : 0;

                                $impuestoTotal = round($subtotal * $impuestoPct / 100, 2);

                                $set('impuesto_total', $impuestoTotal);
                                $set('total', $subtotal + $impuestoTotal - $descuento);
                            }),
                            
                        Forms\Components\TextInput::make('impuesto_total')
                            ->label('Impuesto Total (ISV)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado sobre servicios no exonerados'),
                            
                        Forms\Components\TextInput::make('total')
                            ->label('Total a Pagar')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-bold text-lg'])
                            ->helperText('Subtotal + Impuestos - Descuentos'),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Servicios Incluidos')
                    ->schema([
                        Placeholder::make('servicios_detalle')
                            ->label('')
                            ->content(function () {
                                $consultaId = request()->get('consulta_id');
                                $servicios  = FacturaDetalle::where('consulta_id', $consultaId)
                                                ->whereNull('factura_id')
                                                ->with('servicio')
                                                ->get();

                                if ($servicios->isEmpty()) {
                                    return 'No hay servicios para facturar';
                                }

                                return new \Illuminate\Support\HtmlString(
                                    view('filament.components.servicios-table', [
                                        'detalles' => $servicios,
                                    ])->render()
                                );
                            })
                            ->columnSpanFull(),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('paciente.persona.nombre_completo')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('fecha_emision')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable(),
                    
                TextColumn::make('descuento.nombre')
                    ->label('Descuento')
                    ->placeholder('Sin descuento')
                    ->toggleable(),
                    
                TextColumn::make('descuento_total')
                    ->label('Desc.')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(),
                    
                TextColumn::make('total')
                    ->label('Total')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                    
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'PAGADA' => 'success',
                        'PARCIAL' => 'info',
                        'ANULADA' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'PAGADA' => 'Pagada',
                        'PARCIAL' => 'Parcial',
                        'ANULADA' => 'Anulada',
                    ]),
                    
                Tables\Filters\SelectFilter::make('descuento_id')
                    ->label('Con descuento')
                    ->relationship('descuento', 'nombre')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListFacturas::route('/'),
            'create' => Pages\CreateFacturas::route('/create'),
            'edit' => Pages\EditFacturas::route('/{record}/edit'),
            //'create-wizard' => Pages\CreateFacturaWizard::route('/create-wizard'),
            'view' => Pages\ViewFacturas::route('/{record}'),
        ];
    }
}