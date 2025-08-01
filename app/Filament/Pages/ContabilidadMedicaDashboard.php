<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ContabilidadMedica\Widgets\ContabilidadMedicaOverview;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\Medico;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ContabilidadMedicaDashboard extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Centro de Pagos Médicos';
    protected static ?string $title = 'Centro de Pagos Médicos';
    protected static ?string $slug = 'contabilidad-medica-dashboard';
    protected static ?int $navigationSort = 3;
    
    protected static string $view = 'filament.pages.contabilidad-medica-dashboard';
    
    public static function shouldRegisterNavigation(): bool
    {
        // Solo mostrar para usuarios con acceso a contabilidad
        return true; // Esto se puede ajustar según la lógica de permisos específica
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('nuevoCargo')
                ->label('Nuevo Cargo Médico')
                ->icon('heroicon-m-plus-circle')
                ->color('success')
                ->url(fn (): string => route('filament.admin.resources.contabilidad-medica.cargo-medicos.create')),
                
            Action::make('nuevoPago')
                ->label('Registrar Pago')
                ->icon('heroicon-m-banknotes')
                ->color('primary')
                ->url(fn (): string => route('filament.admin.resources.contabilidad-medica.pago-honorarios.create')),
                
            Action::make('generarReporte')
                ->label('Generar Reporte')
                ->icon('heroicon-m-document-chart-bar')
                ->color('warning')
                ->action(function () {
                    // Lógica para generar reporte
                    $this->dispatch('open-modal', id: 'generate-report-modal');
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            ContabilidadMedicaOverview::class,
        ];
    }
    
    public function table(Table $table): Table
    {
        $centroId = Auth::user()->centro_id;
        
        $query = CargoMedico::query()
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->with('medico');
            
        if ($centroId) {
            $query->where('centro_id', $centroId);
        }
        
        return $table
            ->query($query)
            ->heading('Cargos Médicos Pendientes')
            ->description('Lista de cargos pendientes por pagar')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha de Cargo')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('medico.nombre_completo')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('paciente.nombre_completo')
                    ->label('Paciente')
                    ->searchable(),
                    
                TextColumn::make('total')
                    ->label('Monto')
                    ->money('HNL')
                    ->sortable(),
                    
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'danger',
                        'parcial' => 'warning',
                        'pagado' => 'success',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Tables\Actions\Action::make('pagar')
                    ->label('Registrar Pago')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->url(fn (CargoMedico $record): string => route('filament.admin.resources.contabilidad-medica.pago-honorarios.create', [
                        'medico_id' => $record->medico_id,
                        'cargo_id' => $record->id
                    ])),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('pagoMasivo')
                    ->label('Pago Masivo')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->action(function (array $records) {
                        // Agrupar por médico para pago masivo
                        $agrupados = collect($records)->groupBy('medico_id');
                        
                        // Redireccionar al primer médico con sus cargos seleccionados
                        if ($agrupados->count() > 0) {
                            $medicoId = $agrupados->keys()->first();
                            $cargoIds = $agrupados->first()->pluck('id')->toArray();
                            
                            return redirect()->route('filament.admin.resources.contabilidad-medica.pago-honorarios.create', [
                                'medico_id' => $medicoId,
                                'cargos_ids' => implode(',', $cargoIds)
                            ]);
                        }
                    }),
            ]);
    }
}
