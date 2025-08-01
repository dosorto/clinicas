<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\Atencion;
use App\Models\Servicio;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CalcularPorcentaje extends Page
{
    protected static string $resource = CargoMedicoResource::class;

    protected static string $view = 'filament.resources.contabilidad-medica.cargo-medico-resource.pages.calcular-porcentaje';

    public ?Medico $medico = null;
    public ?ContratoMedico $contrato = null;
    public string $periodo_inicio;
    public string $periodo_fin;
    public float $porcentaje = 0;
    public float $monto_base = 0;
    public float $total_servicios = 0;
    public float $monto_porcentaje = 0;
    public float $subtotal = 0;
    public float $impuesto = 0;
    public float $total = 0;
    
    public function mount(): void
    {
        $medicoId = request()->query('medico_id');
        $this->medico = Medico::with('persona')->find($medicoId);
        
        if ($this->medico) {
            $this->contrato = ContratoMedico::where('medico_id', $medicoId)
                ->where('activo', true)
                ->first();
                
            if ($this->contrato) {
                $this->porcentaje = $this->contrato->porcentaje_servicio ?? 0;
                $this->monto_base = $this->contrato->salario_mensual ?? 0;
            }
        }
        
        $this->periodo_inicio = request()->query('periodo_inicio', date('Y-m-d', strtotime('first day of this month')));
        $this->periodo_fin = request()->query('periodo_fin', date('Y-m-d', strtotime('last day of this month')));
        
        $this->calcularMontos();
    }
    
    public function calcularMontos(): void
    {
        if (!$this->medico || !$this->contrato) {
            return;
        }
        
        // Aquí se conectaría con la tabla de atenciones/servicios para calcular
        // Ejemplo simplificado:
        $this->total_servicios = Atencion::where('medico_id', $this->medico->id)
            ->whereBetween('fecha', [$this->periodo_inicio, $this->periodo_fin])
            ->sum('monto') ?? 0;
            
        $this->monto_porcentaje = $this->total_servicios * ($this->porcentaje / 100);
        $this->subtotal = $this->monto_base + $this->monto_porcentaje;
        $this->impuesto = $this->subtotal * 0.15;
        $this->total = $this->subtotal + $this->impuesto;
    }
    
    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información del Médico')
                ->schema([
                    Placeholder::make('medico_nombre')
                        ->label('Médico')
                        ->content($this->medico ? $this->medico->persona->nombre_completo : 'No seleccionado'),
                        
                    Placeholder::make('contrato_info')
                        ->label('Tipo de Contrato')
                        ->content(function () {
                            if (!$this->contrato) return 'Sin contrato activo';
                            
                            $tipo = [];
                            
                            if ($this->monto_base > 0) {
                                $tipo[] = 'Base: L. ' . number_format($this->monto_base, 2);
                            }
                            
                            if ($this->porcentaje > 0) {
                                $tipo[] = 'Porcentaje: ' . $this->porcentaje . '%';
                            }
                            
                            return implode(' + ', $tipo) ?: 'Indefinido';
                        }),
                ])->columns(2),
                
            Section::make('Periodo de Cálculo')
                ->schema([
                    DatePicker::make('periodo_inicio')
                        ->label('Fecha de Inicio')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->calcularMontos()),
                        
                    DatePicker::make('periodo_fin')
                        ->label('Fecha de Fin')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->calcularMontos()),
                ])->columns(2),
                
            Section::make('Resumen de Servicios')
                ->schema([
                    Placeholder::make('total_servicios_info')
                        ->label('Total Facturado en Servicios')
                        ->content('L. ' . number_format($this->total_servicios, 2)),
                        
                    Placeholder::make('monto_porcentaje_info')
                        ->label('Monto por Porcentaje (' . $this->porcentaje . '%)')
                        ->content('L. ' . number_format($this->monto_porcentaje, 2)),
                ]),
                
            Section::make('Cálculo Final')
                ->schema([
                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->prefix('L')
                        ->default(fn () => $this->subtotal)
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $this->subtotal = floatval($state);
                            $this->impuesto = $this->subtotal * 0.15;
                            $this->total = $this->subtotal + $this->impuesto;
                        }),
                        
                    TextInput::make('impuesto')
                        ->label('Impuesto (15%)')
                        ->prefix('L')
                        ->default(fn () => $this->impuesto)
                        ->disabled()
                        ->numeric(),
                        
                    TextInput::make('total')
                        ->label('Total')
                        ->prefix('L')
                        ->default(fn () => $this->total)
                        ->disabled()
                        ->numeric(),
                ])->columns(3),
                
            Section::make('')
                ->schema([
                    Placeholder::make('instrucciones')
                        ->content(new HtmlString('
                            <div class="text-center p-4 bg-primary-50 rounded-lg border border-primary-200">
                                <h3 class="text-lg font-bold text-primary-700">Instrucciones</h3>
                                <p class="text-sm text-primary-600">
                                    Una vez verificado el monto, haga clic en "Usar este cálculo" para aplicarlo en el formulario de cargo médico.
                                </p>
                            </div>
                        ')),
                ]),
        ]);
    }
    
    public function usarCalculo()
    {
        // Redirigir de vuelta al formulario de creación con los datos calculados
        return redirect()->route('filament.admin.resources.contabilidad-medica.cargo-medicos.create', [
            'subtotal' => $this->subtotal,
            'impuesto_total' => $this->impuesto,
            'total' => $this->total,
            'medico_id' => $this->medico?->id,
            'contrato_id' => $this->contrato?->id,
            'periodo_inicio' => $this->periodo_inicio,
            'periodo_fin' => $this->periodo_fin,
        ]);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('usarCalculo')
                ->label('Usar este cálculo')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action('usarCalculo'),
                
            \Filament\Actions\Action::make('volver')
                ->label('Cancelar y volver')
                ->color('gray')
                ->url(route('filament.admin.resources.contabilidad-medica.cargo-medicos.create'))
        ];
    }
}
