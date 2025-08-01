<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\Medico;
use App\Models\FacturaDetalle;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateLiquidacionHonorario extends CreateRecord
{
    use HasWizard;
    
    protected static string $resource = LiquidacionHonorarioResource::class;
    
    // Configurar el wizard para que ocupe toda la pantalla
    protected int | string | array $columnSpan = 'full';
    
    public function mount(): void
    {
        parent::mount();
        
        // Precargar el centro médico del usuario autenticado
        if (Auth::check() && Auth::user()->centro_id) {
            $this->form->fill([
                'centro_id' => Auth::user()->centro_id,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Información Básica')
                        ->icon('heroicon-o-user')
                        ->description('Seleccione el médico y periodo')
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
                                    // Si hay un solo centro disponible, seleccionarlo automáticamente
                                    $medico = Medico::find($state);
                                    if ($medico) {
                                        $centroUsuario = Auth::user()->centro_id;
                                        if ($centroUsuario) {
                                            $set('centro_id', $centroUsuario);
                                        }
                                    }
                                }),
                                
                            Select::make('centro_id')
                                ->label('Centro Médico')
                                ->relationship('centro', 'nombre_centro')
                                ->required()
                                ->searchable()
                                ->preload(),
                                
                            DatePicker::make('periodo_inicio')
                                ->required()
                                ->label('Fecha de Inicio')
                                ->default(now()->startOfMonth())
                                ->native(false),
                                
                            DatePicker::make('periodo_fin')
                                ->required()
                                ->label('Fecha de Fin')
                                ->default(now()->endOfMonth())
                                ->native(false),
                                
                            Select::make('tipo_liquidacion')
                                ->label('Tipo de Liquidación')
                                ->options([
                                    'servicios' => 'Servicios',
                                    'honorarios' => 'Honorarios',
                                    'mixto' => 'Mixto'
                                ])
                                ->required()
                                ->default('servicios')
                                ->helperText('Seleccione el tipo de liquidación a realizar'),
                        ]),
                        
                    Step::make('Servicios a Liquidar')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->description('Seleccione los servicios a incluir')
                        ->schema([
                            Section::make('Servicios Disponibles')
                                ->description('Seleccione los servicios realizados por el médico en el periodo indicado')
                                ->schema([
                                    CheckboxList::make('servicios_seleccionados')
                                        ->label('Servicios')
                                        ->options(function (callable $get) {
                                            $medicoId = $get('medico_id');
                                            $periodoInicio = $get('periodo_inicio');
                                            $periodoFin = $get('periodo_fin');
                                            
                                            if (!$medicoId || !$periodoInicio || !$periodoFin) {
                                                return [];
                                            }
                                            
                                            // Aquí se obtendrían los servicios disponibles
                                            // En una implementación real, buscaríamos en facturas o registros de servicios
                                            // Por ahora, usamos datos de ejemplo
                                            return [
                                                1 => 'Consulta General - L. 500.00',
                                                2 => 'Procedimiento X - L. 1,200.00',
                                                3 => 'Cirugía Y - L. 5,000.00',
                                                4 => 'Tratamiento Z - L. 800.00',
                                            ];
                                        })
                                        ->required()
                                        ->columns(1)
                                        ->bulkToggleable()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Calcular monto total basado en servicios seleccionados
                                            // En una implementación real, se obtendrían los montos de la base de datos
                                            $montos = [
                                                1 => 500,
                                                2 => 1200,
                                                3 => 5000,
                                                4 => 800,
                                            ];
                                            
                                            $total = 0;
                                            foreach ($state as $servicioId) {
                                                $total += $montos[$servicioId] ?? 0;
                                            }
                                            
                                            $set('monto_total', number_format($total, 2, '.', ''));
                                        }),
                                ]),
                        ]),
                        
                    Step::make('Detalles de Liquidación')
                        ->icon('heroicon-o-currency-dollar')
                        ->description('Verifique los montos de la liquidación')
                        ->schema([
                            TextInput::make('monto_total')
                                ->label('Monto Total')
                                ->required()
                                ->numeric()
                                ->prefix('L')
                                ->placeholder('0.00')
                                ->disabled(),
                                
                            Select::make('estado')
                                ->label('Estado')
                                ->options([
                                    'pendiente' => 'Pendiente',
                                    'parcial' => 'Pago Parcial',
                                    'pagado' => 'Pagado',
                                    'anulado' => 'Anulado'
                                ])
                                ->default('pendiente')
                                ->required(),
                                
                            Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->placeholder('Ingrese cualquier observación o nota adicional sobre esta liquidación')
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ]),
                ]),
            ]);
    }
}
