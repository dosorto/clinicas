<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ViewEntry;

class ViewNomina extends ViewRecord
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_pdf')
                ->label('Generar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return $this->generarPDF();
                }),

            Actions\EditAction::make()
                ->visible(fn () => !$this->record->cerrada),

            Actions\Action::make('cerrar_nomina')
                ->label('Cerrar Nómina')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->visible(fn () => !$this->record->cerrada)
                ->requiresConfirmation()
                ->modalHeading('Cerrar Nómina')
                ->modalDescription('Una vez cerrada la nómina, no podrás editarla ni eliminarla. ¿Estás seguro de que deseas cerrarla?')
                ->modalSubmitActionLabel('Sí, cerrar nómina')
                ->action(function () {
                    $this->record->cerrar();
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Nómina cerrada')
                        ->body('La nómina ha sido cerrada exitosamente.')
                        ->success()
                        ->send();
                        
                    $this->redirect(request()->header('Referer'));
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Datos generales de la nómina')
                    ->icon('heroicon-o-clipboard-document')
                    ->schema([
                        TextEntry::make('empresa')
                            ->label('Empresa'),

                        TextEntry::make('mes')
                            ->label('Mes')
                            ->formatStateUsing(function ($state) {
                                $meses = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                                ];
                                return $meses[(int)$state] ?? $state;
                            }),

                        TextEntry::make('año')
                            ->label('Año'),

                        TextEntry::make('tipo_pago')
                            ->label('Tipo de Pago')
                            ->formatStateUsing(function ($state) {
                                return match($state) {
                                    'mensual' => 'Mensual',
                                    'quincenal' => 'Quincenal',
                                    'semanal' => 'Semanal',
                                    default => ucfirst($state)
                                };
                            })
                            ->badge(),

                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->default('N/A'),

                        IconEntry::make('cerrada')
                            ->label('Estado')
                            ->boolean()
                            ->trueIcon('heroicon-o-lock-closed')
                            ->falseIcon('heroicon-o-lock-open')
                            ->trueColor('danger')
                            ->falseColor('success'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Historial de Pagos de Médicos')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        ViewEntry::make('detalles')
                            ->view('filament.resources.contabilidad-medica.nomina-resource.components.detalle-medicos')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public function generarPDF()
    {
        // Cargar la nómina con relaciones
        $nomina = $this->record->load(['detalles.medico.persona']);
        
        // Obtener el nombre del mes
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $mesNombre = $meses[$nomina->mes] ?? '';
        
        // Preparar datos para el PDF
        $empleados = [];
        $totalNomina = 0;

        foreach ($nomina->detalles as $detalle) {
            $empleados[] = [
                'nombre' => $detalle->medico_nombre,
                'salario' => $detalle->salario_base,
                'deducciones' => $detalle->deducciones,
                'percepciones' => $detalle->percepciones,
                'total' => $detalle->total_pagar,
            ];
            $totalNomina += $detalle->total_pagar;
        }
        
        // Generar HTML para el PDF
        $html = view('pdf.nomina-medica', [
            'nomina' => $nomina,
            'mesNombre' => $mesNombre,
            'empleados' => $empleados,
            'totalNomina' => $totalNomina,
            'fechaGeneracion' => now()->format('d/m/Y H:i:s'),
            'centroMedico' => $nomina->empresa, // Ya contiene el nombre del centro
        ])->render();
        
        // Usar dompdf para generar el PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "nomina_{$mesNombre}_{$nomina->año}.pdf");
    }
}
