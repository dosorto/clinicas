<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NominaMedicaSimpleResource\Pages;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\Medico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class NominaMedicaSimpleResource extends Resource
{
    protected static ?string $model = ContratoMedico::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationLabel = 'Nómina Médica';
    protected static ?string $modelLabel = 'Nómina Médica';
    protected static ?string $pluralModelLabel = 'Nóminas Médicas';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Período de Pago')
                ->description('Configure el período para calcular la nómina médica')
                ->schema([
                    Grid::make(3)->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(Carbon::now()->startOfMonth())
                            ->native(false),
                        
                        DatePicker::make('fecha_fin')
                            ->label('Fecha de Fin')
                            ->required()
                            ->default(Carbon::now()->endOfMonth())
                            ->afterOrEqual('fecha_inicio')
                            ->native(false),
                        
                        Select::make('tipo_nomina')
                            ->label('Tipo de Nómina')
                            ->options([
                                'quincenal_primera' => 'Primera Quincena',
                                'quincenal_segunda' => 'Segunda Quincena',
                                'mensual' => 'Mensual',
                                'personalizada' => 'Período Personalizado',
                            ])
                            ->required()
                            ->default('mensual')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $now = Carbon::now();
                                switch ($state) {
                                    case 'quincenal_primera':
                                        $set('fecha_inicio', $now->startOfMonth()->format('Y-m-d'));
                                        $set('fecha_fin', $now->startOfMonth()->addDays(14)->format('Y-m-d'));
                                        break;
                                    case 'quincenal_segunda':
                                        $set('fecha_inicio', $now->startOfMonth()->addDays(15)->format('Y-m-d'));
                                        $set('fecha_fin', $now->endOfMonth()->format('Y-m-d'));
                                        break;
                                    case 'mensual':
                                        $set('fecha_inicio', $now->startOfMonth()->format('Y-m-d'));
                                        $set('fecha_fin', $now->endOfMonth()->format('Y-m-d'));
                                        break;
                                }
                            }),
                    ]),
                    
                    TextInput::make('descripcion')
                        ->label('Descripción de la Nómina')
                        ->placeholder('Ej: Nómina Médica - Julio 2025')
                        ->columnSpanFull(),
                ])->columns(1),

            Section::make('Médicos a Incluir en la Nómina')
                ->description('Seleccione los médicos que se incluirán en esta nómina')
                ->schema([
                    Repeater::make('medicos_nomina')
                        ->label('')
                        ->required()
                        ->minItems(1)
                        ->schema([
                            Grid::make(8)->schema([
                                Checkbox::make('incluir_en_nomina')
                                    ->label('Incluir')
                                    ->default(true)
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (!$state) {
                                            $set('total_neto_final', 0);
                                        } else {
                                            // Recalcular total
                                            $salario = floatval($get('salario_base') ?? 0);
                                            $servicios = floatval($get('ingresos_servicios') ?? 0);
                                            $retenciones = floatval($get('retenciones_isr') ?? 0);
                                            $set('total_neto_final', $salario + $servicios - $retenciones);
                                        }
                                    })
                                    ->columnSpan(1),
                                
                                TextInput::make('nombre_completo')
                                    ->label('Doctor/a')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                TextInput::make('centro_medico')
                                    ->label('Centro Médico')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                TextInput::make('especialidad')
                                    ->label('Especialidad')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                
                                TextInput::make('porcentaje_servicios')
                                    ->label('% Servicios')
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('%')
                                    ->columnSpan(1),
                                
                                TextInput::make('estado_contrato')
                                    ->label('Estado')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),
                            
                            Grid::make(5)->schema([
                                TextInput::make('salario_base')
                                    ->label('Salario Base')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('L.')
                                    ->columnSpan(1),
                                
                                TextInput::make('ingresos_servicios')
                                    ->label('Ingresos por Servicios')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('L.')
                                    ->columnSpan(1),
                                
                                TextInput::make('total_bruto')
                                    ->label('Total Bruto')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('L.')
                                    ->columnSpan(1),
                                
                                TextInput::make('retenciones_isr')
                                    ->label('Retenciones ISR')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('L.')
                                    ->columnSpan(1),
                                
                                TextInput::make('total_neto_final')
                                    ->label('Total Neto a Pagar')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('L.')
                                    ->extraAttributes(['class' => 'font-bold'])
                                    ->columnSpan(1),
                            ]),
                        ])
                        ->default(function () {
                            $centroUsuario = Filament::auth()->user()?->centro_id;
                            
                            $query = ContratoMedico::with(['medico.persona', 'medico.especialidades', 'centro'])
                                ->where('activo', 'SI');
                                
                            if ($centroUsuario) {
                                $query->where('centro_id', $centroUsuario);
                            }
                            
                            return $query->get()
                                ->map(function ($contrato) {
                                    $salarioBase = floatval($contrato->salario_mensual ?? 0);
                                    $porcentajeServicios = floatval($contrato->porcentaje_servicio ?? 0);
                                    
                                    // Simular cálculos (puedes conectar con la lógica real)
                                    $ingresosServicios = $salarioBase * ($porcentajeServicios / 100);
                                    $totalBruto = $salarioBase + $ingresosServicios;
                                    $retencionesISR = $totalBruto * 0.15; // 15% de ejemplo
                                    $totalNeto = $totalBruto - $retencionesISR;
                                    
                                    return [
                                        'contrato_id' => $contrato->id,
                                        'medico_id' => $contrato->medico_id,
                                        'incluir_en_nomina' => true,
                                        'nombre_completo' => $contrato->medico && $contrato->medico->persona ? 
                                            'Dr. ' . $contrato->medico->persona->nombre_completo : 
                                            'Médico #' . $contrato->medico_id,
                                        'centro_medico' => $contrato->centro->nombre_centro ?? 'Sin asignar',
                                        'especialidad' => $contrato->medico && $contrato->medico->especialidades->count() > 0 ? 
                                            $contrato->medico->especialidades->first()->especialidad : 
                                            'General',
                                        'porcentaje_servicios' => number_format($porcentajeServicios, 1),
                                        'estado_contrato' => $contrato->activo === 'SI' ? 'Activo' : 'Inactivo',
                                        'salario_base' => number_format($salarioBase, 2),
                                        'ingresos_servicios' => number_format($ingresosServicios, 2),
                                        'total_bruto' => number_format($totalBruto, 2),
                                        'retenciones_isr' => number_format($retencionesISR, 2),
                                        'total_neto_final' => number_format($totalNeto, 2),
                                    ];
                                })->toArray();
                        })
                        ->disableItemCreation()
                        ->disableItemDeletion()
                        ->columnSpanFull()
                        ->collapsed(),
                ]),

            Section::make('Resumen de Nómina')
                ->description('Resumen total de la nómina a generar')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('total_medicos')
                            ->label('Total Médicos')
                            ->default(function () {
                                return ContratoMedico::where('activo', 'SI')->count();
                            })
                            ->disabled()
                            ->prefix('#'),
                        
                        TextInput::make('total_salarios_base')
                            ->label('Total Salarios Base')
                            ->default(function () {
                                return number_format(
                                    ContratoMedico::where('activo', 'SI')->sum('salario_mensual'), 
                                    2
                                );
                            })
                            ->disabled()
                            ->prefix('L.'),
                        
                        TextInput::make('total_retenciones')
                            ->label('Total Retenciones')
                            ->default('0.00')
                            ->disabled()
                            ->prefix('L.'),
                        
                        TextInput::make('total_neto_nomina')
                            ->label('Total Neto Nómina')
                            ->default(function () {
                                $total = ContratoMedico::where('activo', 'SI')->sum('salario_mensual');
                                return number_format($total * 0.85, 2); // Ejemplo con 15% retención
                            })
                            ->disabled()
                            ->prefix('L.')
                            ->extraAttributes(['class' => 'font-bold text-green-600']),
                    ]),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                ContratoMedico::query()
                    ->with(['medico.persona', 'medico.especialidades', 'centro'])
                    ->where('activo', 'SI')
                    ->when(Filament::auth()->user()?->centro_id, function ($query, $centroId) {
                        return $query->where('centro_id', $centroId);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('medico_info')
                    ->label('Doctor/a')
                    ->getStateUsing(function (ContratoMedico $record) {
                        if ($record->medico && $record->medico->persona) {
                            return 'Dr. ' . $record->medico->persona->nombre_completo;
                        }
                        return 'Sin médico asignado';
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->getStateUsing(function (ContratoMedico $record) {
                        $centro = $record->centro;
                        if ($centro) {
                            return $centro->nombre_centro;
                        }
                        return 'Sin centro asignado';
                    })
                    ->searchable()
                    ->badge()
                    ->color('success')
                    ->tooltip(function (ContratoMedico $record) {
                        return $record->centro?->direccion ?? 'Sin dirección';
                    }),

                Tables\Columns\TextColumn::make('medico.especialidades.especialidad')
                    ->label('Especialidad')
                    ->getStateUsing(function (ContratoMedico $record) {
                        if ($record->medico && $record->medico->especialidades->count() > 0) {
                            return $record->medico->especialidades->first()->especialidad;
                        }
                        return 'General';
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('salario_mensual')
                    ->label('Salario Base')
                    ->money('HNL')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('porcentaje_servicio')
                    ->label('% Servicios')
                    ->suffix('%')
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('total_estimado')
                    ->label('Total Estimado')
                    ->getStateUsing(function (ContratoMedico $record) {
                        $salario = floatval($record->salario_mensual ?? 0);
                        $porcentaje = floatval($record->porcentaje_servicio ?? 0);
                        $servicios = $salario * ($porcentaje / 100);
                        $total = $salario + $servicios;
                        return 'L. ' . number_format($total, 2);
                    })
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),
            ])
            ->filters([
                // Mantener simple
            ])
            ->actions([
                Tables\Actions\Action::make('nomina_individual')
                    ->label('Nómina Individual')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (ContratoMedico $record) {
                        $fechaInicio = Carbon::now()->startOfMonth();
                        $fechaFin = Carbon::now()->endOfMonth();
                        
                        $url = route('nomina.generar.pdf', [
                            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                            'fecha_fin' => $fechaFin->format('Y-m-d'),
                            'medico_id' => $record->medico_id,
                            'incluir_pagados' => true,
                            'incluir_pendientes' => false,
                        ]);
                        
                        Notification::make()
                            ->title('Generando nómina individual...')
                            ->body('PDF de nómina para Dr. ' . ($record->medico->persona->nombre_completo ?? 'Médico'))
                            ->success()
                            ->send();
                            
                        return redirect($url);
                    }),

                Tables\Actions\Action::make('crear_nomina_personalizada')
                    ->label('Crear Nómina')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(fn () => static::getUrl('create'))
                    ->button(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('nomina_general_mes')
                    ->label('Nómina General del Mes')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('primary')
                    ->action(function () {
                        $fechaInicio = Carbon::now()->startOfMonth();
                        $fechaFin = Carbon::now()->endOfMonth();
                        
                        $url = route('nomina.generar.pdf', [
                            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                            'fecha_fin' => $fechaFin->format('Y-m-d'),
                            'incluir_pagados' => true,
                            'incluir_pendientes' => false,
                        ]);
                        
                        Notification::make()
                            ->title('Generando nómina general...')
                            ->body('PDF para todos los médicos del mes actual')
                            ->success()
                            ->send();
                            
                        return redirect($url);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generar Nómina General')
                    ->modalDescription('Se generará la nómina para todos los médicos activos del mes actual.')
                    ->modalSubmitActionLabel('Generar PDF'),

                Tables\Actions\Action::make('ayuda_nomina')
                    ->label('Ayuda')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('gray')
                    ->modalContent(fn () => new \Illuminate\Support\HtmlString('
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-blue-900 mb-2">🏥 Sistema de Nómina Médica</h3>
                                <p class="text-blue-800">Genere nóminas de manera sencilla para médicos con contratos activos.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-green-50 p-3 rounded">
                                    <h4 class="font-medium text-green-900 mb-1">Funciones:</h4>
                                    <ul class="text-sm text-green-800 space-y-1">
                                        <li>• Nómina individual</li>
                                        <li>• Nómina general</li>
                                        <li>• Períodos personalizados</li>
                                    </ul>
                                </div>
                                <div class="bg-yellow-50 p-3 rounded">
                                    <h4 class="font-medium text-yellow-900 mb-1">Incluye:</h4>
                                    <ul class="text-sm text-yellow-800 space-y-1">
                                        <li>• Salario base</li>
                                        <li>• Servicios médicos</li>
                                        <li>• Retenciones ISR</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    '))
                    ->modalHeading('Ayuda - Nómina Médica')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),
            ])
            ->emptyStateHeading('No hay contratos médicos activos')
            ->emptyStateDescription('Registre contratos médicos para poder generar nóminas')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->defaultSort('fecha_inicio', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNominaMedicaSimples::route('/'),
            'create' => Pages\CreateNominaMedicaSimple::route('/create'),
        ];
    }

    public static function canEdit($record): bool
    {
        return false; // Solo lectura, los contratos se editan en su propio resource
    }

    public static function canDelete($record): bool
    {
        return false; // No se eliminan contratos desde aquí
    }

    public static function canView($record): bool
    {
        return false; // Simplificar, solo crear y listar
    }
}
