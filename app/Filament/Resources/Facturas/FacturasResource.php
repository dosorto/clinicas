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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class FacturasResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static ?string $slug = 'facturas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Determina si una factura es de solo lectura (no editable)
     */
    protected static function esFacturaSoloLectura(?Factura $record): bool
    {
        return $record && $record->estado === 'PAGADA';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Factura')
                    ->schema([
                        Forms\Components\Hidden::make('consulta_id'),
                        Forms\Components\Hidden::make('paciente_id'),
                        Forms\Components\Hidden::make('medico_id'),
                        Forms\Components\Hidden::make('cita_id'),
                    
                        Forms\Components\Placeholder::make('consulta_info')
                            ->label('')
                            ->content(function (?Factura $record, callable $get): \Illuminate\Support\HtmlString {
                                // Crear dependencia del toggle CAI igual que en servicios
                                $usaCai = $get('usa_cai');
                                
                                // Obtener consulta_id igual que en servicios - IMPORTANTE: usar $get también
                                $consultaId = request()->get('consulta_id') ?? $get('consulta_id') ?? $record?->consulta_id;
                                
                                // Información de contexto
                                $pacienteNombre = 'Paciente no encontrado';
                                $medicoNombre = 'Médico no encontrado';
                                $centroNombre = Auth::user()->centro?->nombre_centro ?? 'Centro Médico';
                                $fecha = now()->format('d/m/Y');
                                
                                if ($consultaId) {
                                    $consulta = \App\Models\Consulta::with([
                                        'paciente.persona', 
                                        'medico.persona',
                                        'centro'
                                    ])->find($consultaId);
                                    
                                    if ($consulta) {
                                        $pacienteNombre = $consulta->paciente->persona->nombre_completo ?? 'Paciente no encontrado';
                                        $medicoNombre = $consulta->medico->persona->nombre_completo ?? 'Médico no encontrado';
                                        $centroNombre = $consulta->centro?->nombre_centro ?? Auth::user()->centro?->nombre_centro ?? 'Centro Médico';
                                        $fecha = $consulta->created_at->format('d/m/Y');
                                    }
                                } elseif ($record && $record->exists) {
                                    // Si no hay consulta pero tenemos un registro de factura, usar información de la factura
                                    $pacienteNombre = $record->paciente?->persona?->nombre_completo ?? 'Paciente no encontrado';
                                    $medicoNombre = $record->medico?->persona?->nombre_completo ?? 'Médico no encontrado';
                                    $centroNombre = $record->centro?->nombre_centro ?? Auth::user()->centro?->nombre_centro ?? 'Centro Médico';
                                    $fecha = $record->fecha_emision ? $record->fecha_emision->format('d/m/Y') : $record->created_at->format('d/m/Y');
                                }
                                
                                return new \Illuminate\Support\HtmlString("
                                    <div class='bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700'>
                                        <div class='grid grid-cols-1 md:grid-cols-3 gap-6'>
                                            <!-- Paciente -->
                                            <div class='text-center'>
                                                <div class='flex justify-center mb-3'>
                                                    <div class='w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center'>
                                                        <svg class='w-6 h-6 text-blue-600 dark:text-blue-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class='text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1'>Paciente</p>
                                                <p class='text-sm font-semibold text-gray-900 dark:text-gray-100'>{$pacienteNombre}</p>
                                            </div>
                                            
                                            <!-- Médico -->
                                            <div class='text-center'>
                                                <div class='flex justify-center mb-3'>
                                                    <div class='w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center'>
                                                        <svg class='w-6 h-6 text-green-600 dark:text-green-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class='text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1'>Médico</p>
                                                <p class='text-sm font-semibold text-gray-900 dark:text-gray-100'>{$medicoNombre}</p>
                                            </div>
                                            
                                            <!-- Centro y Fecha -->
                                            <div class='text-center'>
                                                <div class='flex justify-center mb-3'>
                                                    <div class='w-12 h-12 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center'>
                                                        <svg class='w-6 h-6 text-purple-600 dark:text-purple-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class='text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1'>Centro</p>
                                                <p class='text-sm font-semibold text-gray-900 dark:text-gray-100'>{$centroNombre}</p>
                                                <p class='text-xs text-gray-500 dark:text-gray-400 mt-1'>{$fecha}</p>
                                            </div>
                                        </div>
                                    </div>
                                ");
                            }),

                        // Información de facturación
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('numero_factura_display')
                                    ->label('Número de Factura')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function (?Factura $record): string {
                                        if ($record && $record->exists) {
                                            // Cargar la relación si no está cargada
                                            if (!$record->relationLoaded('caiCorrelativo')) {
                                                $record->load('caiCorrelativo');
                                            }
                                            
                                            if ($record->usa_cai && $record->caiCorrelativo) {
                                                return $record->caiCorrelativo->numero_factura;
                                            }
                                            return $record->generarNumeroSinCAI();
                                        }
                                        return 'Se generará automáticamente';
                                    })
                                    ->extraAttributes(['class' => 'font-mono font-bold'])
                                    ->helperText('Generado automáticamente al guardar'),

                                Forms\Components\DatePicker::make('fecha_emision')
                                    ->label('Fecha de Emisión')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Toggle::make('usa_cai')
                                    ->label('¿Emitir con CAI?')
                                    ->default(true)
                                    ->helperText('Active si desea emitir una factura fiscal con CAI')
                                    ->live(),

                                Forms\Components\Select::make('estado')
                                    ->label('Estado')
                                    ->options([
                                        'PENDIENTE' => 'Pendiente',
                                        'PAGADA' => 'Pagada',
                                        'PARCIAL' => 'Pago Parcial',
                                        'ANULADA' => 'Anulada',
                                    ])
                                    ->default('PENDIENTE')
                                    ->required(),
                            ]),

                        // Información CAI (solo cuando está activo)
                        Forms\Components\Placeholder::make('cai_info')
                            ->label('Código CAI')
                            ->content(function (callable $get): \Illuminate\Support\HtmlString {
                                // Crear dependencia del toggle CAI igual que en otros componentes
                                $usaCai = $get('usa_cai');
                                
                                $centroId = Auth::user()->centro_id;
                                $cai = \App\Services\CaiNumerador::obtenerCAIDisponible($centroId);
                                
                                if ($cai) {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-700'>
                                            <div class='flex items-center space-x-3'>
                                                <div class='flex-shrink-0'>
                                                    <div class='w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center'>
                                                        <svg class='w-4 h-4 text-green-600 dark:text-green-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class='flex-1'>
                                                    <p class='text-sm font-mono font-medium text-green-800 dark:text-green-200'>{$cai->cai_codigo}</p>
                                                    <p class='text-xs text-green-600 dark:text-green-400'>CAI Autorizado</p>
                                                </div>
                                            </div>
                                        </div>
                                    ");
                                }
                                
                                return new \Illuminate\Support\HtmlString("
                                    <div class='bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 border border-amber-200 dark:border-amber-700'>
                                        <div class='flex items-center space-x-3'>
                                            <div class='flex-shrink-0'>
                                                <svg class='w-5 h-5 text-amber-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z'></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class='text-sm font-medium text-amber-800 dark:text-amber-200'>Sin CAI disponible</p>
                                                <p class='text-xs text-amber-600 dark:text-amber-400'>Se emitirá como proforma</p>
                                            </div>
                                        </div>
                                    </div>
                                ");
                            })
                            ->visible(fn (Forms\Get $get): bool => $get('usa_cai') === true),
                    ])->columns(1),

                Forms\Components\Section::make('Servicios Incluidos')
                    ->schema([
                        Placeholder::make('servicios_detalle')
                            ->label('')
                            ->content(function (?Factura $record, callable $get): \Illuminate\Support\HtmlString {
                                $servicios = collect();
                                
                                // Intentar obtener servicios desde múltiples fuentes
                                if ($record && $record->exists) {
                                    // Para facturas existentes, obtener los servicios ya asociados
                                    $servicios = $record->detalles()->with('servicio')->get();
                                } else {
                                    // Para facturas nuevas, intentar obtener de la consulta
                                    $consultaId = request()->get('consulta_id') ?? $get('consulta_id');
                                    if ($consultaId) {
                                        // Buscar servicios pendientes de facturación para esta consulta
                                        $serviciosPendientes = FacturaDetalle::where('consulta_id', $consultaId)
                                                        ->whereNull('factura_id')
                                                        ->with(['servicio.impuesto'])
                                                        ->get();
                                        
                                        if ($serviciosPendientes->isNotEmpty()) {
                                            $servicios = $serviciosPendientes;
                                        } else {
                                            // Si no hay servicios pendientes, crear detalles temporales desde la consulta
                                            $consulta = \App\Models\Consulta::with(['examenes'])->find($consultaId);
                                            if ($consulta && $consulta->examenes) {
                                                // Crear objetos temporales de FacturaDetalle para mostrar
                                                $servicios = $consulta->examenes->map(function ($examen) use ($consultaId) {
                                                    // Verificar si es un servicio o un examen
                                                    $servicio = null;
                                                    $precio = 0;
                                                    $nombre = 'Servicio no encontrado';
                                                    $codigo = 'N/A';
                                                    
                                                    // Si es un modelo Servicio directamente
                                                    if ($examen instanceof \App\Models\Servicio) {
                                                        $servicio = $examen;
                                                        $precio = $examen->precio_unitario;
                                                        $nombre = $examen->nombre;
                                                        $codigo = $examen->codigo;
                                                    } else {
                                                        // Si es un examen, buscar el servicio relacionado
                                                        $servicio = \App\Models\Servicio::find($examen->servicio_id ?? $examen->id);
                                                        if ($servicio) {
                                                            $precio = $servicio->precio_unitario;
                                                            $nombre = $servicio->nombre;
                                                            $codigo = $servicio->codigo;
                                                        }
                                                    }
                                                    
                                                    // Crear un objeto temporal que simule FacturaDetalle
                                                    return (object) [
                                                        'servicio_id' => $servicio?->id,
                                                        'consulta_id' => $consultaId,
                                                        'cantidad' => 1,
                                                        'subtotal' => $precio,
                                                        'total_linea' => $precio,
                                                        'impuesto_monto' => 0,
                                                        'descuento_monto' => 0,
                                                        'servicio' => (object) [
                                                            'id' => $servicio?->id,
                                                            'nombre' => $nombre,
                                                            'codigo' => $codigo,
                                                            'precio_unitario' => $precio
                                                        ]
                                                    ];
                                                });
                                            }
                                        }
                                    }
                                }

                                if ($servicios->isEmpty()) {
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='text-center py-8 text-gray-500 dark:text-gray-400'>
                                            <div class='flex justify-center mb-3'>
                                                <svg class='w-8 h-8 text-gray-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'></path>
                                                </svg>
                                            </div>
                                            <p class='font-medium'>No hay servicios para mostrar</p>
                                            <p class='text-sm mt-1'>Los servicios aparecerán aquí una vez que se agreguen a la factura.</p>
                                        </div>
                                    ");
                                }

                                return new \Illuminate\Support\HtmlString(
                                    view('filament.components.servicios-table', [
                                        'detalles' => $servicios,
                                    ])->render()
                                );
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (Suma de servicios)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado automáticamente desde los servicios')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $subtotal = (float) ($state ?? 0);
                                $descuentoTotal = (float) ($get('descuento_total') ?? 0);
                                
                                // Obtener el impuesto real de los servicios
                                $impuestoTotal = (float) (request()->get('impuesto_total') ?? $get('impuesto_total') ?? 0);
                                
                                // Total = Subtotal + Impuesto - Descuento
                                $total = $subtotal + $impuestoTotal - $descuentoTotal;
                                
                                $set('impuesto_total', $impuestoTotal);
                                $set('total', round($total, 2));
                                
                                // Actualizar saldo pendiente también
                                $pagos = $get('pagos') ?? [];
                                $totalPagado = 0;
                                foreach ($pagos as $pago) {
                                    if (is_array($pago) && isset($pago['monto_recibido'])) {
                                        $totalPagado += (float) $pago['monto_recibido'];
                                    }
                                }
                                $saldoPendiente = max(0, $total - $totalPagado);
                                $set('saldo_pendiente', round($saldoPendiente, 2));
                                
                                // Actualizar el estado de la factura según el saldo pendiente
                                if ($saldoPendiente == 0 && $total > 0 && $totalPagado > 0) {
                                    $set('estado', 'PAGADA');
                                } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                    $set('estado', 'PARCIAL');
                                } else {
                                    $set('estado', 'PENDIENTE');
                                }
                            })
                            ->default(function () {
                                // Obtener automáticamente el subtotal desde la URL
                                return request()->get('subtotal') ?? 0;
                            })
                            ->afterStateHydrated(function (TextInput $component, $state) {
                                // Asegurar que el valor esté disponible desde el inicio
                                if (!$state) {
                                    $component->state(request()->get('subtotal') ?? 0);
                                }
                            }),
                            
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
                                
                                // Obtener el impuesto real de los servicios
                                $impuestoTotal = (float) (request()->get('impuesto_total') ?? $get('impuesto_total') ?? 0);
                                
                                // Total = Subtotal + Impuesto - Descuento
                                $total = $subtotal + $impuestoTotal - $descuentoTotal;
                                
                                $set('descuento_total', round($descuentoTotal, 2));
                                $set('impuesto_total', $impuestoTotal);
                                $set('total', round($total, 2));
                                
                                // Actualizar saldo pendiente también
                                $pagos = $get('pagos') ?? [];
                                $totalPagado = 0;
                                foreach ($pagos as $pago) {
                                    if (is_array($pago) && isset($pago['monto_recibido'])) {
                                        $totalPagado += (float) $pago['monto_recibido'];
                                    }
                                }
                                $saldoPendiente = max(0, $total - $totalPagado);
                                $set('saldo_pendiente', round($saldoPendiente, 2));
                                
                                // Actualizar el estado de la factura según el saldo pendiente
                                if ($saldoPendiente == 0 && $total > 0 && $totalPagado > 0) {
                                    $set('estado', 'PAGADA');
                                } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                    $set('estado', 'PARCIAL');
                                } else {
                                    $set('estado', 'PENDIENTE');
                                }
                                
                                // Forzar actualización de campos display
                                $set('trigger_update', time());
                                
                                // Actualizar directamente los campos de display
                                $totalConDescuento = $subtotal + $impuestoTotal - $descuentoTotal;
                                $set('total_a_pagar_display', number_format($totalConDescuento, 2));
                                $set('saldo_pendiente_display', number_format(max(0, $totalConDescuento - (float)($get('total_pagado') ?? 0)), 2));
                            }),
                            
                        Forms\Components\TextInput::make('descuento_total')
                            ->label('Monto del Descuento')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado automáticamente según el descuento seleccionado'),

                        Forms\Components\TextInput::make('impuesto_total')
                            ->label('Impuesto Total (ISV)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Total de impuestos de los servicios - Calculado automáticamente')
                            ->default(function () {
                                // Obtener el total de impuestos desde la URL (viene de los servicios)
                                return request()->get('impuesto_total') ?? 0;
                            })
                            ->afterStateHydrated(function (TextInput $component, $state) {
                                // Asegurar que el valor esté disponible desde el inicio
                                if (!$state) {
                                    $component->state(request()->get('impuesto_total') ?? 0);
                                }
                            })
                            ->live(),
                            
                        Forms\Components\TextInput::make('total')
                            ->label('Total a Pagar')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-bold text-lg'])
                            ->helperText('Subtotal + Impuesto - Descuento')
                            ->default(function (callable $get) {
                                $subtotal = (float) (request()->get('subtotal') ?? $get('subtotal') ?? 0);
                                $impuesto = (float) (request()->get('impuesto_total') ?? $get('impuesto_total') ?? 0);
                                $descuento = (float) ($get('descuento_total') ?? 0);
                                return round($subtotal + $impuesto - $descuento, 2);
                            })
                            ->afterStateHydrated(function (TextInput $component, $state, callable $get) {
                                // Recalcular el total al cargar el formulario
                                $subtotal = (float) (request()->get('subtotal') ?? $get('subtotal') ?? 0);
                                $impuesto = (float) (request()->get('impuesto_total') ?? $get('impuesto_total') ?? 0);
                                $descuento = (float) ($get('descuento_total') ?? 0);
                                $total = round($subtotal + $impuesto - $descuento, 2);
                                $component->state($total);
                            })
                            ->live(),
                            
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->maxLength(500)
                            ->placeholder('Notas adicionales sobre la factura...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Pago')
                    ->schema([
                        Repeater::make('pagos')
                            ->label('Métodos de Pago')
                            ->relationship('pagos')
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Método de Pago')
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, callable $get): array {
                                \Log::info('💰 Creando pago', [
                                    'monto' => $data['monto_recibido'] ?? 'N/A',
                                    'tipo_pago' => $data['tipo_pago_id'] ?? 'N/A',
                                    'paciente_id_inicial' => $data['paciente_id'] ?? 'N/A'
                                ]);
                                
                                // ESTRATEGIA SIMPLIFICADA Y ROBUSTA PARA PACIENTE_ID
                                $pacienteId = null;
                                
                                // Estrategia 1: Del campo del pago directamente
                                if (isset($data['paciente_id']) && $data['paciente_id'] && $data['paciente_id'] !== '?') {
                                    $pacienteId = $data['paciente_id'];
                                    \Log::info('✅ Paciente ID obtenido del pago directo', ['paciente_id' => $pacienteId]);
                                }
                                
                                // Estrategia 2: Del formulario principal (nivel factura)
                                if (!$pacienteId) {
                                    $pacienteIdForm = $get('../../paciente_id');
                                    if ($pacienteIdForm && $pacienteIdForm !== '?') {
                                        $pacienteId = $pacienteIdForm;
                                        \Log::info('✅ Paciente ID obtenido del formulario', ['paciente_id' => $pacienteId]);
                                    }
                                }
                                
                                // Estrategia 3: De la consulta como último recurso
                                if (!$pacienteId) {
                                    $consultaId = $get('../../consulta_id') ?? request()->get('consulta_id');
                                    if ($consultaId) {
                                        try {
                                            $consulta = \App\Models\Consulta::find($consultaId);
                                            if ($consulta && $consulta->paciente_id) {
                                                $pacienteId = $consulta->paciente_id;
                                                \Log::info('✅ Paciente ID obtenido de consulta', [
                                                    'consulta_id' => $consultaId,
                                                    'paciente_id' => $pacienteId
                                                ]);
                                            }
                                        } catch (\Exception $e) {
                                            \Log::error('Error al obtener consulta', ['error' => $e->getMessage()]);
                                        }
                                    }
                                }
                                
                                // Validación final más específica
                                if (!$pacienteId || $pacienteId === '?' || $pacienteId === '' || !is_numeric($pacienteId)) {
                                    \Log::error('❌ No se pudo determinar paciente_id válido', [
                                        'data_original' => $data,
                                        'consulta_id' => $get('../../consulta_id'),
                                        'paciente_id_form' => $get('../../paciente_id'),
                                        'request_consulta_id' => request()->get('consulta_id')
                                    ]);
                                    
                                    // Mensaje más específico
                                    throw new \Exception('No se pudo determinar el paciente para este pago. Datos disponibles: ' . 
                                        'Consulta ID: ' . ($get('../../consulta_id') ?? 'null') . ', ' .
                                        'Paciente Form: ' . ($get('../../paciente_id') ?? 'null') . '. ' .
                                        'Por favor, verifique que la factura tenga los datos del paciente correctamente cargados.');
                                }
                                
                                // Asegurar que es un entero válido
                                $pacienteId = (int) $pacienteId;
                                
                                // Completar datos requeridos
                                $data['paciente_id'] = $pacienteId;
                                $data['monto_devolucion'] = $data['monto_devolucion'] ?? 0;
                                $data['fecha_pago'] = $data['fecha_pago'] ?? now();
                                $data['created_by'] = $data['created_by'] ?? Auth::id();
                                $data['centro_id'] = $data['centro_id'] ?? Auth::user()->centro_id;
                                
                                \Log::info('✅ Pago preparado para crear', [
                                    'paciente_id' => $data['paciente_id'],
                                    'monto_recibido' => $data['monto_recibido'],
                                    'tipo_pago_id' => $data['tipo_pago_id']
                                ]);
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, callable $get): array {
                                \Log::info('💰 Actualizando pago', [
                                    'id' => $data['id'] ?? 'nuevo',
                                    'paciente_id_inicial' => $data['paciente_id'] ?? 'N/A'
                                ]);
                                
                                // Obtener paciente_id - FALLBACK ROBUSTO  
                                $pacienteId = $data['paciente_id'] ?? null;
                                
                                // Si no está disponible, obtenerlo del formulario principal
                                if (!$pacienteId) {
                                    $pacienteId = $get('../../paciente_id');
                                }
                                
                                // Si aún no tenemos paciente_id, obtenerlo de la consulta_id
                                if (!$pacienteId) {
                                    $consultaId = $get('../../consulta_id') ?? request()->get('consulta_id');
                                    if ($consultaId) {
                                        $consulta = \App\Models\Consulta::find($consultaId);
                                        if ($consulta) {
                                            $pacienteId = $consulta->paciente_id;
                                        }
                                    }
                                }
                                
                                // Validar que tenemos un paciente_id válido
                                if (!$pacienteId || $pacienteId === '?' || $pacienteId === '') {
                                    \Log::error('❌ No se pudo determinar paciente_id al actualizar', [
                                        'data_original' => $data,
                                        'consulta_id' => $get('../../consulta_id'),
                                        'paciente_id_form' => $get('../../paciente_id')
                                    ]);
                                    throw new \Exception('No se pudo determinar el paciente para este pago. Verifique que la factura tenga un paciente asignado.');
                                }
                                
                                // Completar datos básicos
                                $data['paciente_id'] = $pacienteId;
                                $data['monto_devolucion'] = $data['monto_devolucion'] ?? 0;
                                $data['fecha_pago'] = $data['fecha_pago'] ?? now();
                                $data['updated_by'] = Auth::id();
                                $data['centro_id'] = $data['centro_id'] ?? Auth::user()->centro_id;
                                
                                return $data;
                            })
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
                                    ->minValue(0.01)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Calcular totales cada vez que cambia un monto
                                        $pagos = $get('../../pagos') ?? [];
                                        $totalPagado = 0;
                                        foreach ($pagos as $pago) {
                                            if (is_array($pago) && isset($pago['monto_recibido'])) {
                                                $totalPagado += (float) $pago['monto_recibido'];
                                            }
                                        }
                                        
                                        // Calcular total a pagar desde el campo total
                                        $totalAPagar = (float) ($get('../../total') ?? 0);
                                        
                                        // Calcular saldo pendiente (lo que falta por pagar)
                                        $saldoPendiente = max(0, $totalAPagar - $totalPagado);
                                        
                                        // Calcular cambio (si hay exceso)
                                        $cambio = max(0, $totalPagado - $totalAPagar);
                                        
                                        // Actualizar campos directamente
                                        $set('../../total_pagado', number_format($totalPagado, 2));
                                        $set('../../saldo_pendiente', round($saldoPendiente, 2));
                                        $set('../../cambio', number_format($cambio, 2));
                                        
                                        // Actualizar el estado de la factura según el saldo pendiente
                                        if ($saldoPendiente == 0 && $totalAPagar > 0) {
                                            $set('../../estado', 'PAGADA');
                                        } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                            $set('../../estado', 'PARCIAL');
                                        } else {
                                            $set('../../estado', 'PENDIENTE');
                                        }
                                    })
                                    ->columnSpan(1),
                                    
                                // Campos ocultos - VERSIÓN MEJORADA Y MÁS AGRESIVA
                                Forms\Components\Hidden::make('paciente_id')
                                    ->default(function (callable $get) {
                                        // Estrategia múltiple para obtener paciente_id
                                        
                                        // 1. Del formulario principal
                                        $pacienteId = $get('../../paciente_id');
                                        if ($pacienteId && $pacienteId !== '?') {
                                            return $pacienteId;
                                        }
                                        
                                        // 2. De la consulta desde URL
                                        $consultaId = request()->get('consulta_id') ?? $get('../../consulta_id');
                                        if ($consultaId) {
                                            try {
                                                $consulta = \App\Models\Consulta::find($consultaId);
                                                if ($consulta && $consulta->paciente_id) {
                                                    return $consulta->paciente_id;
                                                }
                                            } catch (\Exception $e) {
                                                // Si hay error, continuar con otros métodos
                                            }
                                        }
                                        
                                        // 3. Si estamos editando, del record actual
                                        if (request()->route('record')) {
                                            try {
                                                $factura = \App\Models\Factura::find(request()->route('record'));
                                                if ($factura && $factura->paciente_id) {
                                                    return $factura->paciente_id;
                                                }
                                            } catch (\Exception $e) {
                                                // Si hay error, continuar
                                            }
                                        }
                                        
                                        return null;
                                    })
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Forzar la propagación del paciente_id si se actualiza
                                        if ($state && $state !== '?') {
                                            \Log::info('Paciente ID actualizado en campo hidden', ['paciente_id' => $state]);
                                        }
                                    }),
                                    
                                Forms\Components\Hidden::make('centro_id')
                                    ->default(Auth::user()->centro_id),
                                    
                                Forms\Components\Hidden::make('fecha_pago')
                                    ->default(now()),
                                    
                                Forms\Components\Hidden::make('created_by')
                                    ->default(Auth::id()),
                                    
                                Forms\Components\Hidden::make('monto_devolucion')
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->addActionLabel('Agregar método de pago')
                            ->deletable()
                            ->reorderable(false)
                            ->maxItems(5)
                            ->minItems(0)
                            ->collapsible()
                            ->live() // Hacer el Repeater reactivo
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Recalcular totales cuando cambia el estado del Repeater (agregar/eliminar)
                                $pagos = $state ?? [];
                                $totalPagado = 0;
                                
                                foreach ($pagos as $pago) {
                                    if (is_array($pago) && isset($pago['monto_recibido']) && $pago['monto_recibido'] !== '') {
                                        $totalPagado += (float) $pago['monto_recibido'];
                                    }
                                }
                                
                                // Calcular total a pagar desde el campo total
                                $totalAPagar = (float) ($get('total') ?? 0);
                                
                                // Calcular saldo pendiente y cambio
                                $saldoPendiente = max(0, $totalAPagar - $totalPagado);
                                $cambio = max(0, $totalPagado - $totalAPagar);
                                
                                // Actualizar campos reactivos
                                $set('total_pagado', number_format($totalPagado, 2));
                                $set('saldo_pendiente', round($saldoPendiente, 2));
                                $set('cambio', number_format($cambio, 2));
                                
                                // Actualizar estado de la factura
                                if ($saldoPendiente == 0 && $totalAPagar > 0) {
                                    $set('estado', 'PAGADA');
                                } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                    $set('estado', 'PARCIAL');
                                } else {
                                    $set('estado', 'PENDIENTE');
                                }
                            })
                            ->disabled(fn (?Factura $record) => self::esFacturaSoloLectura($record)),

                        // Botón de pago rápido
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('pago_completo')
                                ->label('Pagar Total Completo')
                                ->icon('heroicon-m-banknotes')
                                ->color('success')
                                ->action(function (callable $set, callable $get) {
                                    // Calcular el total real con descuento aplicado
                                    $subtotal = (float) ($get('subtotal') ?? 0);
                                    $impuesto = (float) ($get('impuesto_total') ?? 0);
                                    $descuento = (float) ($get('descuento_total') ?? 0);
                                    $totalConDescuento = $subtotal + $impuesto - $descuento;
                                    $totalPagado = (float) ($get('total_pagado') ?? 0);
                                    $saldoPendiente = max(0, $totalConDescuento - $totalPagado);
                                    
                                    if ($saldoPendiente > 0) {
                                        // Agregar un nuevo pago con el saldo pendiente
                                        $pagosActuales = $get('pagos') ?? [];
                                        $pagosActuales[] = [
                                            'tipo_pago_id' => 1, // Efectivo por defecto
                                            'monto_recibido' => $saldoPendiente,
                                            'paciente_id' => $get('paciente_id'),
                                            'centro_id' => Auth::user()->centro_id,
                                            'fecha_pago' => now(),
                                            'created_by' => Auth::id(),
                                        ];
                                        
                                        $set('pagos', $pagosActuales);
                                        $set('total_pagado', number_format($totalConDescuento, 2));
                                        $set('saldo_pendiente', 0);
                                        $set('cambio', 0);
                                        $set('estado', 'PAGADA');
                                    }
                                })
                                ->visible(function (callable $get, ?Factura $record) {
                                    // No mostrar si la factura ya está pagada
                                    if (self::esFacturaSoloLectura($record)) {
                                        return false;
                                    }
                                    
                                    // Calcular el total real con descuento aplicado
                                    $subtotal = (float) ($get('subtotal') ?? 0);
                                    $impuesto = (float) ($get('impuesto_total') ?? 0);
                                    $descuento = (float) ($get('descuento_total') ?? 0);
                                    $totalConDescuento = $subtotal + $impuesto - $descuento;
                                    $totalPagado = (float) ($get('total_pagado') ?? 0);
                                    return $totalPagado < $totalConDescuento;
                                })
                        ])->columnSpanFull(),

                        // Resumen de totales
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('total_a_pagar_display')
                                    ->label('Total a Pagar')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->formatStateUsing(function (callable $get) {
                                        $subtotal = (float) ($get('subtotal') ?? 0);
                                        $impuesto = (float) ($get('impuesto_total') ?? 0);
                                        $descuento = (float) ($get('descuento_total') ?? 0);
                                        $totalAPagar = $subtotal + $impuesto - $descuento;
                                        return number_format($totalAPagar, 2);
                                    })
                                    ->extraAttributes(['class' => 'font-bold text-lg text-blue-600']),
                                    
                                Forms\Components\TextInput::make('total_pagado')
                                    ->label('Total Pagado')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->formatStateUsing(function (callable $get) {
                                        // Calcular total pagado desde el Repeater
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        foreach ($pagos as $pago) {
                                            $totalPagado += (float) ($pago['monto_recibido'] ?? 0);
                                        }
                                        return number_format($totalPagado, 2);
                                    })
                                    ->extraAttributes(['class' => 'font-bold text-green-600'])
                                    ->helperText('Suma de todos los métodos de pago'),
                                    
                                Forms\Components\TextInput::make('cambio')
                                    ->label('Cambio a Devolver')
                                    ->prefix('L.')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->live()
                                    ->formatStateUsing(function (callable $get) {
                                        // Calcular total a pagar
                                        $subtotal = (float) ($get('subtotal') ?? 0);
                                        $impuesto = (float) ($get('impuesto_total') ?? 0);
                                        $descuento = (float) ($get('descuento_total') ?? 0);
                                        $totalConDescuento = $subtotal + $impuesto - $descuento;
                                        
                                        // Calcular total pagado desde el Repeater
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        foreach ($pagos as $pago) {
                                            $totalPagado += (float) ($pago['monto_recibido'] ?? 0);
                                        }
                                        
                                        $cambio = max(0, $totalPagado - $totalConDescuento);
                                        return number_format($cambio, 2);
                                    })
                                    ->extraAttributes(function (callable $get) {
                                        // Calcular si hay cambio para mostrar color apropiado
                                        $subtotal = (float) ($get('subtotal') ?? 0);
                                        $impuesto = (float) ($get('impuesto_total') ?? 0);
                                        $descuento = (float) ($get('descuento_total') ?? 0);
                                        $totalConDescuento = $subtotal + $impuesto - $descuento;
                                        
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        foreach ($pagos as $pago) {
                                            $totalPagado += (float) ($pago['monto_recibido'] ?? 0);
                                        }
                                        
                                        return [
                                            'class' => $totalPagado > $totalConDescuento 
                                                ? 'font-bold text-orange-600' 
                                                : 'font-bold text-gray-600'
                                        ];
                                    })
                                    ->helperText('Se calcula automáticamente si el pago excede el total'),
                                    
                                Forms\Components\TextInput::make('saldo_pendiente')
                                    ->label('Saldo Pendiente')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('L.')
                                    ->readOnly()
                                    ->extraAttributes(['class' => 'font-bold text-lg'])
                                    ->helperText('Empieza igual al Total a Pagar y se reduce con cada pago')
                                    ->default(function (callable $get) {
                                        $subtotal = (float) (request()->get('subtotal') ?? $get('subtotal') ?? 0);
                                        $impuesto = (float) (request()->get('impuesto_total') ?? $get('impuesto_total') ?? 0);
                                        $descuento = (float) ($get('descuento_total') ?? 0);
                                        return round($subtotal + $impuesto - $descuento, 2);
                                    })
                                    ->afterStateHydrated(function (TextInput $component, $state, callable $get) {
                                        // Recalcular igual que el total al cargar el formulario
                                        $subtotal = (float) (request()->get('subtotal') ?? $get('subtotal') ?? 0);
                                        $impuesto = (float) (request()->get('impuesto_total') ?? $get('impuesto_total') ?? 0);
                                        $descuento = (float) ($get('descuento_total') ?? 0);
                                        $total = round($subtotal + $impuesto - $descuento, 2);
                                        
                                        // Calcular total pagado
                                        $pagos = $get('pagos') ?? [];
                                        $totalPagado = 0;
                                        foreach ($pagos as $pago) {
                                            if (is_array($pago) && isset($pago['monto_recibido'])) {
                                                $totalPagado += (float) $pago['monto_recibido'];
                                            }
                                        }
                                        
                                        $saldo = max(0, $total - $totalPagado);
                                        $component->state($saldo);
                                    })
                                    ->live()
                                    ->extraAttributes(function (callable $get) {
                                        // Usar el valor directo del campo saldo_pendiente
                                        $saldoPendiente = (float) ($get('saldo_pendiente') ?? 0);
                                        $totalAPagar = (float) ($get('total') ?? 0);
                                        $totalPagado = (float) str_replace(',', '', $get('total_pagado') ?? '0');
                                        
                                        if ($saldoPendiente == 0 && $totalAPagar > 0) {
                                            return ['class' => 'font-bold text-lg text-green-600']; // Pagado completo
                                        } elseif ($saldoPendiente > 0 && $totalPagado > 0) {
                                            return ['class' => 'font-bold text-lg text-yellow-600']; // Pago parcial
                                        } else {
                                            return ['class' => 'font-bold text-lg text-red-600']; // Sin pagos
                                        }
                                    
                                    })
                                    ->helperText('Empieza igual al Total a Pagar y se reduce con cada pago'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')
                    ->label('Número')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Factura $record): string => 
                        $record->usa_cai ? 'success' : 'warning'
                    )
                    ->formatStateUsing(function (Factura $record): string {
                        if ($record->usa_cai && $record->caiCorrelativo) {
                            return $record->caiCorrelativo->numero_factura;
                        }
                        return $record->generarNumeroSinCAI();
                    })
                    ->description(fn (Factura $record): ?string => 
                        $record->usa_cai ? 'Factura Fiscal' : 'Recibo/Proforma'
                    ),

                TextColumn::make('cai_codigo')
                    ->label('CAI')
                    ->getStateUsing(fn (Factura $record): ?string => $record->codigo_cai)
                    ->placeholder('Sin CAI')
                    ->limit(15)
                    ->tooltip(fn (Factura $record): ?string => $record->codigo_cai)
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                    
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
                    
                TextColumn::make('impuesto_total')
                    ->label('Impuestos')
                    ->money('HNL')
                    ->alignEnd()
                    ->color('orange')
                    ->toggleable(),

                TextColumn::make('descuento_total')
                    ->label('Descuento')
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
                    ->color('success')
                    ->description(function (Factura $record): string {
                        $pagado = $record->montoPagado();
                        $saldo = $record->saldoPendiente();
                        
                        if ($saldo <= 0) {
                            return 'Totalmente pagada';
                        } elseif ($pagado > 0) {
                            return 'Saldo: L. ' . number_format($saldo, 2);
                        }
                        
                        return 'Sin pagos';
                    }),
                    
                TextColumn::make('metodos_pago')
                    ->label('Métodos de Pago')
                    ->getStateUsing(function (Factura $record): string {
                        $pagos = $record->pagos()->with('tipoPago')->get();
                        
                        if ($pagos->isEmpty()) {
                            return 'Sin pagos';
                        }
                        
                        $resumen = [];
                        foreach ($pagos as $pago) {
                            $tipo = $pago->tipoPago->nombre ?? 'N/A';
                            $monto = $pago->monto_recibido;
                            
                            if (!isset($resumen[$tipo])) {
                                $resumen[$tipo] = 0;
                            }
                            $resumen[$tipo] += $monto;
                        }
                        
                        $lineas = [];
                        foreach ($resumen as $tipo => $monto) {
                            $lineas[] = "{$tipo}: L. " . number_format($monto, 2);
                        }
                        
                        return implode("\n", $lineas);
                    })
                    ->html()
                    ->wrap()
                    ->extraAttributes(['style' => 'white-space: pre-line; font-size: 0.8em;'])
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'PAGADA' => 'success',
                        'PARCIAL' => 'info',
                        'ANULADA' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'heroicon-m-clock',
                        'PAGADA' => 'heroicon-m-check-circle',
                        'PARCIAL' => 'heroicon-m-currency-dollar',
                        'ANULADA' => 'heroicon-m-x-circle',
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
                
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Factura $record): string => route('factura.pdf', $record))
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('preview_pdf')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Factura $record): string => route('factura.pdf.preview', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('download_pdfs')
                        ->label('Descargar PDFs')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $facturaIds = $records->pluck('id')->toArray();
                            
                            // Redirigir a la URL con los IDs como parámetros GET
                            $url = route('facturas.pdf.lote') . '?' . http_build_query(['factura_ids' => $facturaIds]);
                            
                            return redirect($url);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
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
