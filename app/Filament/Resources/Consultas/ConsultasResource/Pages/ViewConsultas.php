<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Consulta;
use App\Models\Receta;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewConsultas extends ViewRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botón principal para crear nueva receta
            Actions\Action::make('crear_receta')
                ->label('Nueva Receta')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->size('lg')
                ->url(function (Consulta $record) {
                    return \App\Filament\Resources\Receta\RecetaResource::getUrl('create-simple', [], true) .
                           '?paciente_id=' . $record->paciente_id .
                           '&consulta_id=' . $record->id .
                           '&medico_id=' . $record->medico_id;
                })
                ->openUrlInNewTab(false),

            // Separator
            Actions\Action::make('separator')
                ->label('')
                ->disabled()
                ->hidden(),

            // Botón para editar la consulta
            Actions\EditAction::make()
                ->color('warning'),

            // Botón para volver al listado
            Actions\Action::make('back')
                ->label('Volver')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Consulta')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                        ]),

                Infolists\Components\Section::make('Participantes')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('paciente_info')
                                    ->label('Paciente')
                                    ->state(function (Consulta $record): string {
                                        if ($record->paciente && $record->paciente->persona) {
                                            $persona = $record->paciente->persona;
                                            $nombre = $persona->nombre_completo;
                                            $dni = $persona->dni ? " - DNI: {$persona->dni}" : '';
                                            $telefono = $persona->telefono ? " - Tel: {$persona->telefono}" : '';
                                            return $nombre . $dni . $telefono;
                                        }
                                        return 'No disponible';
                                    })
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('medico_info')
                                    ->label('Médico')
                                    ->state(function (Consulta $record): string {
                                        if ($record->medico && $record->medico->persona) {
                                            $persona = $record->medico->persona;
                                            $nombre = $persona->nombre_completo;
                                            $dni = $persona->dni ? " - DNI: {$persona->dni}" : '';
                                            $colegiacion = $record->medico->numero_colegiacion ? " - Col: {$record->medico->numero_colegiacion}" : '';
                                            return $nombre . $dni . $colegiacion;
                                        }

                                        // Fallback manual si no se carga la relación
                                        if ($record->medico_id) {
                                            $medico = \App\Models\Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                                            if ($medico && $medico->persona) {
                                                $persona = $medico->persona;
                                                $nombre = $persona->nombre_completo;
                                                $dni = $persona->dni ? " - DNI: {$persona->dni}" : '';
                                                $colegiacion = $medico->numero_colegiacion ? " - Col: {$medico->numero_colegiacion}" : '';
                                                return $nombre . $dni . $colegiacion;
                                            }
                                        }

                                        return 'No disponible';
                                    })
                                    ->copyable(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Detalles de Consulta')
                    ->schema([
                        Infolists\Components\Section::make('Diagnóstico')
                            ->schema([
                                Infolists\Components\TextEntry::make('diagnostico')
                                    ->hiddenLabel()
                                    ->placeholder('Sin diagnóstico registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin diagnóstico registrado')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Infolists\Components\Section::make('Tratamiento')
                            ->schema([
                                Infolists\Components\TextEntry::make('tratamiento')
                                    ->hiddenLabel()
                                    ->placeholder('Sin tratamiento registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin tratamiento registrado')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Infolists\Components\Section::make('Observaciones')
                            ->schema([
                                Infolists\Components\TextEntry::make('observaciones')
                                    ->hiddenLabel()
                                    ->placeholder('Sin observaciones registradas')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin observaciones registradas')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),
                    ]),

                // Sección de recetas asociadas
                Infolists\Components\Section::make('Recetas Médicas')
                    ->schema([
                        Infolists\Components\TextEntry::make('recetas_info')
                            ->label('')
                            ->state(function (Consulta $record): string {
                                $recetas = $record->recetas()->get();

                                if ($recetas->isEmpty()) {
                                    return 'No hay recetas asociadas a esta consulta.';
                                }

                                $resultado = '';
                                foreach ($recetas as $index => $receta) {
                                    $numero = $index + 1;
                                    $fecha = $receta->fecha_receta ? \Carbon\Carbon::parse($receta->fecha_receta)->format('d/m/Y H:i') : 'Sin fecha';

                                    $resultado .= "📋 RECETA #{$numero} - {$fecha}\n";

                                    if ($receta->diagnostico) {
                                        $resultado .= "🔍 Diagnóstico: {$receta->diagnostico}\n";
                                    }

                                    $resultado .= "\n💊 MEDICAMENTOS:\n{$receta->medicamentos}\n";
                                    $resultado .= "\n📝 INDICACIONES:\n{$receta->indicaciones}\n";

                                    if ($index < $recetas->count() - 1) {
                                        $resultado .= "\n" . str_repeat('─', 60) . "\n\n";
                                    }
                                }

                                return $resultado;
                            })
                            ->columnSpanFull()
                            ->copyable()
                            ->extraAttributes([
                                'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; padding: 15px; border-radius: 8px; border: 2px solid; line-height: 1.6; font-family: monospace;',
                                'class' => 'bg-gradient-to-r from-blue-50 to-green-50 border-blue-300 text-gray-800 dark:from-blue-900 dark:to-green-900 dark:border-blue-600 dark:text-gray-100'
                            ]),

                        // Botones de acción para las recetas
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('gestionar_recetas')
                                ->label('Ver todas las recetas')
                                ->icon('heroicon-o-document-text')
                                ->color('info')
                                ->url(function (Consulta $record) {
                                    return \App\Filament\Resources\Receta\RecetaResource::getUrl('index');
                                })
                                ->openUrlInNewTab(false)
                                ->visible(function (Consulta $record) {
                                    return $record->recetas()->count() > 0;
                                }),

                            Infolists\Components\Actions\Action::make('imprimir_recetas')
                                ->label('Imprimir Recetas')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->action(function (Consulta $record) {
                                    // Redirigir a una vista de impresión con todas las recetas de la consulta
                                    return redirect()->route('recetas.imprimir.consulta', ['consulta' => $record->id]);
                                })
                                ->visible(function (Consulta $record) {
                                    return $record->recetas()->count() > 0;
                                }),
                        ])
                        ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }
}
