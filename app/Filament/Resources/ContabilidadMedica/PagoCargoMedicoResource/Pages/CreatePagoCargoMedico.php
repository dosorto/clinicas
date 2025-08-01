<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\PagoCargoMedico;
use App\Models\Centros_Medico;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePagoCargoMedico extends CreateRecord
{
    use HasWizard;
    
    protected static string $resource = PagoCargoMedicoResource::class;
    
    // Capturar parámetros pasados por URL
    public ?int $cargo_id = null;
    public ?int $centro_id = null;
    
    // Configurar el wizards para que ocupe toda la pantalla
    protected int | string | array $columnSpan = 'full';
    
    public function mount(): void
    {
        parent::mount();
        
        // Si tenemos parámetros, configuramos los valores por defecto en el formulario
        $this->form->fill([
            'cargo_id' => $this->cargo_id,
            'centro_id' => $this->centro_id ?? (Auth::check() ? Auth::user()->centro_id : null),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Selección de Cargo')
                        ->icon('heroicon-o-document-text')
                        ->description('Seleccione el cargo a pagar')
                        ->schema([
                            Select::make('cargo_id')
                                ->label('Cargo Médico')
                                ->options(function () {
                                    // Obtener cargos pendientes o con pago parcial
                                    return CargoMedico::whereIn('estado', ['pendiente', 'parcial'])
                                        ->with('medico.persona')
                                        ->get()
                                        ->mapWithKeys(function ($cargo) {
                                            return [
                                                $cargo->id => "#{$cargo->id} - {$cargo->medico->persona->nombre_completo} - {$cargo->descripcion} - L. {$cargo->total}"
                                            ];
                                        });
                                })
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $cargo = CargoMedico::with('centro')->find($state);
                                        if ($cargo) {
                                            $set('centro_id', $cargo->centro_id);
                                            
                                            // Calcular monto pendiente
                                            $pagado = PagoCargoMedico::where('cargo_id', $state)
                                                ->sum('monto_pagado');
                                            $pendiente = $cargo->total - $pagado;
                                            
                                            $set('monto_pendiente', $pendiente);
                                            $set('monto_pagado', $pendiente);
                                        }
                                    }
                                }),
                                
                            TextInput::make('monto_pendiente')
                                ->label('Monto Pendiente')
                                ->prefix('L')
                                ->disabled()
                                ->dehydrated(false),
                                
                            Select::make('centro_id')
                                ->label('Centro Médico')
                                ->relationship('centro', 'nombre_centro')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ]),
                        
                    Step::make('Detalles del Pago')
                        ->icon('heroicon-o-banknotes')
                        ->description('Ingrese los detalles del pago')
                        ->schema([
                            DatePicker::make('fecha_pago')
                                ->label('Fecha de Pago')
                                ->required()
                                ->default(now())
                                ->native(false),
                                
                            TextInput::make('monto_pagado')
                                ->label('Monto a Pagar')
                                ->required()
                                ->numeric()
                                ->prefix('L')
                                ->placeholder('0.00'),
                                
                            Select::make('metodo_pago')
                                ->label('Método de Pago')
                                ->options([
                                    'efectivo' => 'Efectivo',
                                    'transferencia' => 'Transferencia',
                                    'cheque' => 'Cheque',
                                    'tarjeta' => 'Tarjeta',
                                    'otro' => 'Otro'
                                ])
                                ->required()
                                ->default('transferencia'),
                                
                            TextInput::make('referencia')
                                ->label('Referencia')
                                ->placeholder('Número de cheque, referencia de transferencia, etc.')
                                ->maxLength(255),
                        ]),
                        
                    Step::make('Confirmación')
                        ->icon('heroicon-o-check-circle')
                        ->description('Confirme los detalles del pago')
                        ->schema([
                            Section::make('Resumen del Pago')
                                ->schema([
                                    Forms\Components\Placeholder::make('cargo_info')
                                        ->label('Cargo Médico')
                                        ->content(function (callable $get) {
                                            $cargoId = $get('cargo_id');
                                            if (!$cargoId) return 'No seleccionado';
                                            
                                            $cargo = CargoMedico::with('medico.persona')->find($cargoId);
                                            if (!$cargo) return 'No encontrado';
                                            
                                            return "#{$cargo->id} - {$cargo->medico->persona->nombre_completo} - {$cargo->descripcion}";
                                        }),
                                        
                                    Forms\Components\Placeholder::make('monto_info')
                                        ->label('Monto a Pagar')
                                        ->content(function (callable $get) {
                                            $monto = $get('monto_pagado');
                                            return 'L. ' . number_format($monto, 2);
                                        }),
                                        
                                    Forms\Components\Placeholder::make('metodo_info')
                                        ->label('Método de Pago')
                                        ->content(function (callable $get) {
                                            $metodo = $get('metodo_pago');
                                            $referencia = $get('referencia');
                                            return ucfirst($metodo) . ($referencia ? " - Ref: {$referencia}" : '');
                                        }),
                                        
                                    Forms\Components\Placeholder::make('fecha_info')
                                        ->label('Fecha de Pago')
                                        ->content(function (callable $get) {
                                            $fecha = $get('fecha_pago');
                                            return $fecha ? date('d/m/Y', strtotime($fecha)) : 'No definida';
                                        }),
                                ]),
                                
                            Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->placeholder('Ingrese cualquier observación o nota adicional sobre este pago')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ]),
                ])->columnSpanFull(),
            ])->columns(1);
    }
    
    protected function afterCreate(): void
    {
        // Actualizar el estado del cargo según corresponda
        $cargo = CargoMedico::find($this->record->cargo_id);
        if ($cargo) {
            $totalPagado = PagoCargoMedico::where('cargo_id', $cargo->id)
                ->sum('monto_pagado');
                
            if ($totalPagado >= $cargo->total) {
                $cargo->estado = 'pagado';
            } else if ($totalPagado > 0) {
                $cargo->estado = 'parcial';
            }
            
            $cargo->save();
        }
    }
}
