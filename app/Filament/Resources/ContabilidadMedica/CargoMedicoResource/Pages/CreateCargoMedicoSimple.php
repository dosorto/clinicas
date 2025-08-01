<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\Centros_Medico;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCargoMedicoSimple extends CreateRecord
{
    protected static string $resource = CargoMedicoResource::class;
    
    protected static ?string $title = 'Crear Cargo Médico';
    
    // Personalizar el botón de guardar para que sea más visible
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('GUARDAR CARGO MÉDICO')
                ->color('primary')
                ->size('lg')
                ->icon('heroicon-o-check')
        ];
    }
    
    public function mount(): void
    {
        parent::mount();
        
        $data = [];
        
        // Precargar el centro médico del usuario autenticado
        if (Auth::check() && Auth::user()->centro_id) {
            $data['centro_id'] = Auth::user()->centro_id;
        }
        
        // Recuperar datos de la pantalla de cálculo de porcentaje (si existen)
        if (request()->has('subtotal')) {
            $data['subtotal'] = request()->query('subtotal');
            $data['impuesto_total'] = request()->query('impuesto_total');
            $data['total'] = request()->query('total');
            $data['medico_id'] = request()->query('medico_id');
            $data['contrato_id'] = request()->query('contrato_id');
            $data['periodo_inicio'] = request()->query('periodo_inicio');
            $data['periodo_fin'] = request()->query('periodo_fin');
        }
        
        if (!empty($data)) {
            $this->form->fill($data);
        }
    }
    
    // Botones de acción para la cabecera
    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcular')
                ->label('Asistente de cálculo')
                ->color('warning')
                ->icon('heroicon-o-calculator')
                ->url(route('filament.admin.resources.contabilidad-medica.cargo-medicos.calcular-porcentaje')),
                
            Action::make('cancelar')
                ->label('Cancelar')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información Básica')
                            ->description('Seleccione el médico y contrato')
                            ->schema([
                                Select::make('medico_id')
                                    ->label('Médico')
                                    ->relationship('medico', 'persona_id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->persona->nombre_completo)
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Limpiar el contrato si cambia el médico
                                        $set('contrato_id', null);
                                        
                                        // Preseleccionar el centro médico del usuario automáticamente
                                        $centroUsuario = Auth::user()->centro_id;
                                        if ($centroUsuario) {
                                            $set('centro_id', $centroUsuario);
                                        }
                                        
                                        // Cargar automáticamente el contrato activo si solo hay uno
                                        $medico = Medico::find($state);
                                        if ($medico) {
                                            $contratos = ContratoMedico::where('medico_id', $state)
                                                ->where('activo', true)
                                                ->get();
                                                
                                            if ($contratos->count() === 1) {
                                                $set('contrato_id', $contratos->first()->id);
                                            }
                                        }
                                    }),
                                    
                                Select::make('contrato_id')
                                    ->label('Contrato')
                                    ->options(function (callable $get) {
                                        $medicoId = $get('medico_id');
                                        if (!$medicoId) return [];
                                        
                                        $centroId = Auth::user()->centro_id;
                                        
                                        // Filtrar contratos por médico Y centro (si es posible)
                                        $query = ContratoMedico::where('medico_id', $medicoId)
                                            ->where('activo', true);
                                        
                                        if ($centroId) {
                                            $query->where('centro_id', $centroId);
                                        }
                                        
                                        return $query->get()->mapWithKeys(function ($contrato) {
                                            // Mostrar datos más detallados del contrato
                                            $tipo = $contrato->salario_mensual > 0 ? 'Mensual' : 'Por servicios';
                                            $monto = $contrato->salario_mensual > 0 
                                                ? 'L. ' . number_format($contrato->salario_mensual, 2) . '/mes' 
                                                : $contrato->porcentaje_servicio . '% por servicio';
                                            
                                            return [$contrato->id => "Contrato #{$contrato->id} - {$tipo} ({$monto}) - " . 
                                                date('d/m/Y', strtotime($contrato->fecha_inicio)) . " al " . 
                                                date('d/m/Y', strtotime($contrato->fecha_fin))];
                                        });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $contrato = ContratoMedico::find($state);
                                            if ($contrato) {
                                                if ($contrato->salario_mensual > 0) {
                                                    // Si es salario mensual, establecer automáticamente
                                                    $subtotal = $contrato->salario_mensual;
                                                    $impuesto = $subtotal * 0.15;
                                                    $total = $subtotal + $impuesto;
                                                    
                                                    $set('subtotal', $subtotal);
                                                    $set('impuesto_total', number_format($impuesto, 2, '.', ''));
                                                    $set('total', number_format($total, 2, '.', ''));
                                                } else {
                                                    // Para contratos por porcentaje, mostrar mensaje más informativo
                                                    $porcentaje = $contrato->porcentaje_servicio ?? 0;
                                                    Notification::make()
                                                        ->warning()
                                                        ->title('Contrato por porcentaje (' . $porcentaje . '%)')
                                                        ->body('Este médico tiene un contrato por porcentaje de servicios. Debe calcular e ingresar el monto manualmente basado en sus servicios realizados.')
                                                        ->actions([
                                                            \Filament\Notifications\Actions\Action::make('calcular')
                                                                ->label('Asistente de cálculo')
                                                                ->url(route('filament.admin.resources.contabilidad-medica.cargo-medicos.calcular-porcentaje', [
                                                                    'medico_id' => $contrato->medico_id,
                                                                    'periodo_inicio' => date('Y-m-d', strtotime('first day of this month')),
                                                                    'periodo_fin' => date('Y-m-d', strtotime('last day of this month')),
                                                                ]))
                                                        ])
                                                        ->persistent()
                                                        ->send();
                                                }
                                            }
                                        }
                                    }),
                                    
                                Select::make('centro_id')
                                    ->label('Centro Médico')
                                    ->options(function () {
                                        // Solo mostrar el centro del usuario actual
                                        if (Auth::check() && Auth::user()->centro_id) {
                                            $centro = Centros_Medico::find(Auth::user()->centro_id);
                                            if ($centro) {
                                                return [$centro->id => $centro->nombre_centro];
                                            }
                                        }
                                        return [];
                                    })
                                    ->required()
                                    ->disabled() // Deshabilitar para que no se pueda cambiar
                                    ->dehydrated() // Mantener el valor cuando se envíe el formulario
                            ])->columns(2),
                            
                        Section::make('Periodo y Descripción')
                            ->description('Defina el periodo y descripción del cargo')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('periodo_inicio')
                                            ->required()
                                            ->label('Fecha de Inicio')
                                            ->default(now()->startOfMonth())
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                // Si hay un valor, generar la descripción automáticamente
                                                if ($state) {
                                                    $inicio = \Carbon\Carbon::parse($state);
                                                    $fin = $inicio->copy()->endOfMonth();
                                                    $set('periodo_fin', $fin);
                                                    
                                                    // Generar descripción sugerida
                                                    $nombreMes = ucfirst($inicio->translatedFormat('F Y'));
                                                    $set('descripcion', "Servicios médicos del mes de {$nombreMes}");
                                                }
                                            }),
                                            
                                        DatePicker::make('periodo_fin')
                                            ->required()
                                            ->label('Fecha de Fin')
                                            ->default(now()->endOfMonth())
                                            ->native(false),
                                    ]),
                                    
                                TextInput::make('descripcion')
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Descripción')
                                    ->placeholder('Ej: Servicios médicos del mes de Julio 2025')
                                    ->helperText('Descripción detallada del cargo que se está creando')
                                    ->columnSpanFull(),
                            ]),
                            
                        Section::make('Valores del Cargo')
                            ->description('Indique los montos del cargo')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('subtotal')
                                            ->required()
                                            ->numeric()
                                            ->prefix('L')
                                            ->label('Subtotal')
                                            ->placeholder('0.00')
                                            ->helperText('Valor base del cargo antes de impuestos')
                                            ->live()
                                            ->extraAttributes(['class' => 'border-primary-500 bg-primary-50'])
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                // Calcular impuesto (15%) y total automáticamente
                                                $subtotal = floatval($state ?? 0);
                                                $impuesto = $subtotal * 0.15;
                                                $total = $subtotal + $impuesto;
                                                
                                                $set('impuesto_total', $impuesto);
                                                $set('total', $total);
                                            }),
                                            
                                        TextInput::make('impuesto_total')
                                            ->required()
                                            ->numeric()
                                            ->prefix('L')
                                            ->label('Impuesto (15%)')
                                            ->placeholder('0.00')
                                            ->disabled()
                                            ->dehydrated(),
                                            
                                        TextInput::make('total')
                                            ->required()
                                            ->numeric()
                                            ->prefix('L')
                                            ->label('Total')
                                            ->placeholder('0.00')
                                            ->disabled()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'border-success-500 bg-success-50']),
                                    ]),
                                
                                Select::make('estado')
                                    ->label('Estado')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'parcial' => 'Pago Parcial',
                                        'pagado' => 'Pagado',
                                        'anulado' => 'Anulado'
                                    ])
                                    ->default('pendiente')
                                    ->required()
                                    ->helperText('Los cargos nuevos normalmente inician como Pendientes'),
                            ]),
                            
                        Section::make('Observaciones')
                            ->description('Añada notas adicionales si es necesario')
                            ->collapsed()
                            ->schema([
                                Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->placeholder('Ingrese cualquier observación o nota adicional sobre este cargo')
                                    ->maxLength(65535)
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
    
    // Agregar un mensaje después de guardar
    protected function afterCreate(): void
    {
        // Notificar que el cargo ha sido creado exitosamente
        Notification::make()
            ->success()
            ->title('Cargo médico creado')
            ->body('El cargo médico ha sido creado exitosamente')
            ->send();
    }
    
    protected function getRedirectUrl(): string
    {
        // Redirigir a la lista de cargos médicos
        return $this->getResource()::getUrl('index');
    }
}
