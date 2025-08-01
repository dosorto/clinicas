<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePagoHonorarioSimple extends CreateRecord
{
    protected static string $resource = PagoHonorarioResource::class;
    
    protected static ?string $title = 'Registrar Pago de Honorarios';
    
    // Personalizar el botón de guardar para que sea más visible
    // Sobreescribir el método para asegurarse de que el botón esté visible
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('GUARDAR PAGO')
                ->color('success')
                ->size('lg')
                ->icon('heroicon-o-banknotes')
                ->extraAttributes(['class' => 'text-xl py-3 font-bold']),
        ];
    }
    
    // Asegurarse de que el botón de enviar esté visible
    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
    
    // Configurar ubicación del botón
    protected function getFormActionsPosition(): string
    {
        return 'inside';
    }
    
    // Botones de acción para la cabecera
    protected function getHeaderActions(): array
    {
        return [
            Action::make('verLiquidaciones')
                ->label('Ver Liquidaciones')
                ->color('info')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(route('filament.admin.resources.contabilidad-medica.liquidacion-honorarios.index')),
                
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
                        Section::make('Selección de Liquidación')
                            ->description('Seleccione la liquidación para realizar el pago')
                            ->schema([
                                Select::make('liquidacion_id')
                                    ->label('Liquidación')
                                    ->options(function () {
                                        // Obtener solo liquidaciones que no estén completamente pagadas
                                        return LiquidacionHonorario::query()
                                            ->where('estado', '!=', 'pagado')
                                            ->get()
                                            ->mapWithKeys(function ($liquidacion) {
                                                $medico = $liquidacion->cargoMedico->medico->persona->nombre_completo ?? 'Desconocido';
                                                $estado = ucfirst($liquidacion->estado);
                                                $monto = number_format($liquidacion->monto_total, 2);
                                                
                                                return [
                                                    $liquidacion->id => "Liquidación #{$liquidacion->id} - Dr. {$medico} - L. {$monto} ({$estado})"
                                                ];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (!$state) return;
                                        
                                        $liquidacion = LiquidacionHonorario::find($state);
                                        if (!$liquidacion) return;
                                        
                                        // Calcular el monto pendiente
                                        $pagosRealizados = PagoHonorario::where('liquidacion_id', $liquidacion->id)
                                            ->where('estado', '!=', 'anulado')
                                            ->sum('monto');
                                            
                                        $montoPendiente = $liquidacion->monto_total - $pagosRealizados;
                                        
                                        // Llenar automáticamente los campos con los datos de la liquidación
                                        $set('cargo_medico_id', $liquidacion->cargo_medico_id);
                                        $set('monto_pendiente', number_format($montoPendiente, 2, '.', ''));
                                        $set('monto', number_format($montoPendiente, 2, '.', ''));
                                        $set('fecha_pago', now()->format('Y-m-d'));
                                        
                                        // Generar automáticamente el concepto
                                        $medicoNombre = $liquidacion->cargoMedico->medico->persona->nombre_completo ?? 'Dr.';
                                        $referencia = $liquidacion->cargoMedico->descripcion ?? 'servicios médicos';
                                        $set('concepto', "Pago de honorarios a {$medicoNombre} por {$referencia}");
                                        
                                        // Si es el monto completo, establecer pago completo
                                        if ($montoPendiente == $liquidacion->monto_total) {
                                            $set('es_pago_completo', true);
                                        } else {
                                            $set('es_pago_completo', false);
                                        }
                                    }),
                                    
                                TextInput::make('cargo_medico_id')
                                    ->label('Cargo médico')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Este campo se llena automáticamente al seleccionar la liquidación'),
                            ]),
                            
                        Section::make('Información del Pago')
                            ->description('Detalle los datos del pago a realizar')
                            ->schema([
                                TextInput::make('monto_pendiente')
                                    ->label('Monto pendiente')
                                    ->prefix('L')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText('Monto pendiente de pago para esta liquidación'),
                                    
                                TextInput::make('monto')
                                    ->label('Monto a pagar')
                                    ->required()
                                    ->numeric()
                                    ->prefix('L')
                                    ->placeholder('0.00')
                                    ->live()
                                    ->extraAttributes(['class' => 'border-success-500 bg-success-50'])
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $montoPendiente = floatval($get('monto_pendiente') ?? 0);
                                        $montoActual = floatval($state ?? 0);
                                        
                                        // Verificar si el monto actual es igual al pendiente para marcar como pago completo
                                        if (abs($montoActual - $montoPendiente) < 0.01) {
                                            $set('es_pago_completo', true);
                                        } else {
                                            $set('es_pago_completo', false);
                                        }
                                    }),
                                    
                                DatePicker::make('fecha_pago')
                                    ->label('Fecha de pago')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                                    
                                Select::make('metodo_pago')
                                    ->label('Método de pago')
                                    ->options([
                                        'efectivo' => 'Efectivo',
                                        'cheque' => 'Cheque',
                                        'transferencia' => 'Transferencia Bancaria',
                                        'tarjeta' => 'Tarjeta de Crédito/Débito',
                                        'otro' => 'Otro'
                                    ])
                                    ->required()
                                    ->default('efectivo'),
                                    
                                TextInput::make('referencia_pago')
                                    ->label('Referencia de pago')
                                    ->helperText('Número de cheque, referencia de transferencia, etc.')
                                    ->maxLength(255)
                                    ->placeholder('Ej: Cheque #001234'),
                                    
                                Toggle::make('es_pago_completo')
                                    ->label('¿Es pago completo?')
                                    ->helperText('Marque si este pago cubre la totalidad del monto pendiente')
                                    ->default(true)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            // Si se marca como pago completo, establecer el monto pendiente como monto a pagar
                                            $montoPendiente = floatval($get('monto_pendiente') ?? 0);
                                            $set('monto', number_format($montoPendiente, 2, '.', ''));
                                        }
                                    }),
                                    
                                TextInput::make('concepto')
                                    ->label('Concepto')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: Pago de honorarios al Dr. Juan Pérez por servicios médicos')
                                    ->columnSpanFull(),
                                    
                                Toggle::make('generar_recibo')
                                    ->label('Generar recibo automáticamente')
                                    ->helperText('Active para generar y enviar un recibo después de guardar')
                                    ->default(true),
                                    
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('guardarPagoAction')
                                        ->label('GUARDAR PAGO')
                                        ->button()
                                        ->size('lg')
                                        ->color('success')
                                        ->icon('heroicon-o-banknotes')
                                        ->iconPosition('after')
                                        ->extraAttributes([
                                            'class' => 'w-full justify-center text-xl font-bold py-3 mt-4'
                                        ])
                                        ->action(fn () => $this->create())
                                ])->columnSpanFull()
                            ])->columns(2),
                            
                        Section::make('Observaciones')
                            ->description('Añada notas adicionales si es necesario')
                            ->collapsed()
                            ->schema([
                                Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->placeholder('Ingrese cualquier observación o nota adicional sobre este pago')
                                    ->maxLength(65535)
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Establecer el usuario que realiza el pago
        $data['usuario_id'] = Auth::id();
        
        // Establecer el estado como "completado" o "parcial" según corresponda
        $data['estado'] = $data['es_pago_completo'] ? 'completado' : 'parcial';
        
        // Eliminar campos que no pertenecen al modelo
        unset($data['es_pago_completo']);
        unset($data['monto_pendiente']);
        unset($data['generar_recibo']);
        unset($data['cargo_medico_id']);
        
        return $data;
    }
    
    // Actualizar el estado de la liquidación después de guardar
    protected function afterCreate(): void
    {
        // Obtener la liquidación relacionada
        $pago = $this->record;
        $liquidacion = LiquidacionHonorario::find($pago->liquidacion_id);
        
        if ($liquidacion) {
            // Calcular pagos totales
            $pagosTotales = PagoHonorario::where('liquidacion_id', $liquidacion->id)
                ->where('estado', '!=', 'anulado')
                ->sum('monto');
                
            // Determinar el nuevo estado de la liquidación
            if ($pagosTotales >= $liquidacion->monto_total) {
                $liquidacion->estado = 'pagado';
            } else if ($pagosTotales > 0) {
                $liquidacion->estado = 'parcial';
            }
            
            $liquidacion->save();
            
            // Actualizar también el cargo médico asociado
            if ($liquidacion->cargoMedico) {
                $cargoMedico = $liquidacion->cargoMedico;
                
                if ($liquidacion->estado === 'pagado') {
                    $cargoMedico->estado = 'pagado';
                } else if ($liquidacion->estado === 'parcial') {
                    $cargoMedico->estado = 'parcial';
                }
                
                $cargoMedico->save();
            }
            
            // Generar recibo si se solicitó
            $generarRecibo = $this->data['generar_recibo'] ?? false;
            if ($generarRecibo) {
                // Aquí iría la lógica para generar y enviar el recibo
                // Por ejemplo:
                Notification::make()
                    ->success()
                    ->title('Recibo generado')
                    ->body('El recibo ha sido generado y está listo para descargar')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('download')
                            ->label('Descargar')
                            ->url(route('filament.admin.resources.contabilidad-medica.pago-honorarios.generar-recibo', ['pagoId' => $pago->id]))
                            ->openUrlInNewTab()
                    ])
                    ->send();
            }
        }
        
        // Notificar que el pago ha sido creado exitosamente
        Notification::make()
            ->success()
            ->title('Pago registrado')
            ->body('El pago de honorarios ha sido registrado exitosamente')
            ->send();
    }
    
    protected function getRedirectUrl(): string
    {
        // Redirigir a la lista de pagos
        return $this->getResource()::getUrl('index');
    }
}
