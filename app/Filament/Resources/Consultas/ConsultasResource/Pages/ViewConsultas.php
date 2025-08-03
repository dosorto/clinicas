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
                    return \App\Filament\Resources\Receta\RecetaResource::getUrl('create-simple') .
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
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('📅 Fecha de Consulta')
                                    ->dateTime('d/m/Y H:i')
                                    ->color('primary')
                                    ->weight('semibold')
                                    ->extraAttributes([
                                        'style' => 'padding: 8px; border-radius: 6px; border: 1px solid;',
                                        'class' => 'bg-blue-50 border-blue-200 dark:bg-blue-900 dark:border-blue-600'
                                    ]),

                                Infolists\Components\TextEntry::make('paciente_info')
                                    ->label('👤 Paciente')
                                    ->state(function (Consulta $record): string {
                                        if ($record->paciente && $record->paciente->persona) {
                                            $persona = $record->paciente->persona;
                                            $nombre = $persona->nombre_completo;
                                            $dni = $persona->dni ? "\nDNI: {$persona->dni}" : '';
                                            $telefono = $persona->telefono ? "\nTel: {$persona->telefono}" : '';
                                            return $nombre . $dni . $telefono;
                                        }
                                        return 'No disponible';
                                    })
                                    ->color('success')
                                    ->weight('medium')
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; padding: 8px; border-radius: 6px; border: 1px solid;',
                                        'class' => 'bg-green-50 border-green-200 dark:bg-green-900 dark:border-green-600'
                                    ])
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('medico_info')
                                    ->label('👨‍⚕️ Médico')
                                    ->state(function (Consulta $record): string {
                                        if ($record->medico && $record->medico->persona) {
                                            $persona = $record->medico->persona;
                                            $nombre = $persona->nombre_completo;
                                            $dni = $persona->dni ? "\nDNI: {$persona->dni}" : '';
                                            $colegiacion = $record->medico->numero_colegiacion ? "\nCol: {$record->medico->numero_colegiacion}" : '';
                                            return $nombre . $dni . $colegiacion;
                                        }

                                        // Fallback manual si no se carga la relación
                                        if ($record->medico_id) {
                                            $medico = \App\Models\Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                                            if ($medico && $medico->persona) {
                                                $persona = $medico->persona;
                                                $nombre = $persona->nombre_completo;
                                                $dni = $persona->dni ? "\nDNI: {$persona->dni}" : '';
                                                $colegiacion = $medico->numero_colegiacion ? "\nCol: {$medico->numero_colegiacion}" : '';
                                                return $nombre . $dni . $colegiacion;
                                            }
                                        }

                                        return 'No disponible';
                                    })
                                    ->color('warning')
                                    ->weight('medium')
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; padding: 8px; border-radius: 6px; border: 1px solid;',
                                        'class' => 'bg-orange-50 border-orange-200 dark:bg-orange-900 dark:border-orange-600'
                                    ])
                                    ->copyable(),
                            ]),
                        ])
                    ->description('Información principal de la consulta médica')
                    ->icon('heroicon-o-information-circle'),

                Infolists\Components\Section::make('Detalles de Consulta')
                    ->schema([
                        Infolists\Components\Grid::make(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('diagnostico')
                                    ->label(' Diagnóstico')
                                    ->placeholder('Sin diagnóstico registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin diagnóstico registrado')
                                    ->copyable()
                                    ->extraAttributes(function ($state): array {
                                        $content = $state ?: 'Sin diagnóstico registrado';
                                        $lineCount = substr_count($content, "\n") + 1;
                                        $charCount = strlen($content);

                                        // Calcular altura dinámica ultra compacta
                                        $minHeight = 25; // Reducido de 30 a 25
                                        $lineHeight = 18; // Reducido de 20 a 18
                                        $maxHeight = 100; // Reducido de 120 a 100

                                        $estimatedHeight = max($minHeight, min($maxHeight, $lineCount * $lineHeight + 12));
                                        $needsScroll = ($lineCount * $lineHeight + 12) > $maxHeight;

                                        return [
                                            'style' => "white-space: pre-line; text-align: left; word-wrap: break-word; " .
                                                      ($needsScroll ? "max-height: {$maxHeight}px; overflow-y: auto;" : "min-height: {$estimatedHeight}px;") .
                                                      " padding: 6px; border-radius: 6px; border: 1px solid; line-height: 1.3; margin-bottom: 6px;", // Ultra compacto
                                            'class' => 'bg-blue-50 border-blue-200 text-gray-900 dark:bg-blue-900 dark:border-blue-600 dark:text-gray-100'
                                        ];
                                    }),

                                Infolists\Components\TextEntry::make('tratamiento')
                                    ->label(' Tratamiento')
                                    ->placeholder('Sin tratamiento registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin tratamiento registrado')
                                    ->copyable()
                                    ->extraAttributes(function ($state): array {
                                        $content = $state ?: 'Sin tratamiento registrado';
                                        $lineCount = substr_count($content, "\n") + 1;

                                        // Calcular altura dinámica ultra compacta
                                        $minHeight = 25;
                                        $lineHeight = 18;
                                        $maxHeight = 100;

                                        $estimatedHeight = max($minHeight, min($maxHeight, $lineCount * $lineHeight + 12));
                                        $needsScroll = ($lineCount * $lineHeight + 12) > $maxHeight;

                                        return [
                                            'style' => "white-space: pre-line; text-align: left; word-wrap: break-word; " .
                                                      ($needsScroll ? "max-height: {$maxHeight}px; overflow-y: auto;" : "min-height: {$estimatedHeight}px;") .
                                                      " padding: 6px; border-radius: 6px; border: 1px solid; line-height: 1.3; margin-bottom: 6px;",
                                            'class' => 'bg-green-50 border-green-200 text-gray-900 dark:bg-green-900 dark:border-green-600 dark:text-gray-100'
                                        ];
                                    }),

                                Infolists\Components\TextEntry::make('observaciones')
                                    ->label(' Observaciones')
                                    ->placeholder('Sin observaciones registradas')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin observaciones registradas')
                                    ->copyable()
                                    ->extraAttributes(function ($state): array {
                                        $content = $state ?: 'Sin observaciones registradas';
                                        $lineCount = substr_count($content, "\n") + 1;

                                        // Calcular altura dinámica ultra compacta
                                        $minHeight = 25;
                                        $lineHeight = 18;
                                        $maxHeight = 100;

                                        $estimatedHeight = max($minHeight, min($maxHeight, $lineCount * $lineHeight + 12));
                                        $needsScroll = ($lineCount * $lineHeight + 12) > $maxHeight;

                                        return [
                                            'style' => "white-space: pre-line; text-align: left; word-wrap: break-word; " .
                                                      ($needsScroll ? "max-height: {$maxHeight}px; overflow-y: auto;" : "min-height: {$estimatedHeight}px;") .
                                                      " padding: 6px; border-radius: 6px; border: 1px solid; line-height: 1.3; margin-bottom: 6px;",
                                            'class' => 'bg-yellow-50 border-yellow-200 text-gray-900 dark:bg-yellow-900 dark:border-yellow-600 dark:text-gray-100'
                                        ];
                                    }),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                // Sección de recetas asociadas
                Infolists\Components\Section::make('Recetas Médicas')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('recetas')
                            ->label('')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('fecha_receta')
                                                    ->label(' Fecha')
                                                    ->date('d/m/Y')
                                                    ->color('primary')
                                                    ->weight('semibold')
                                                    ->placeholder('Sin fecha registrada'),

                                                Infolists\Components\TextEntry::make('id')
                                                    ->label('Receta #')
                                                    ->formatStateUsing(fn ($state) => "RECETA #{$state}")
                                                    ->color('warning')
                                                    ->weight('bold'),

                                                Infolists\Components\Actions::make([
                                                    Infolists\Components\Actions\Action::make('imprimir')
                                                        ->label('Imprimir')
                                                        ->icon('heroicon-o-printer')
                                                        ->color('success')
                                                        ->size('sm')
                                                        ->url(fn (Receta $record): string => route('recetas.imprimir', $record))
                                                        ->openUrlInNewTab(true),
                                                ]),
                                            ]),

                                        // Separador visual
                                        Infolists\Components\TextEntry::make('separator')
                                            ->label('')
                                            ->state('')
                                            ->extraAttributes([
                                                'style' => 'height: 1px; border-top: 1px solid; margin: 15px 0;',
                                                'class' => 'border-gray-300 dark:border-gray-600'
                                            ])
                                            ->columnSpanFull(),

                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('medicamentos')
                                                    ->label('💊 Medicamentos')
                                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin medicamentos especificados')
                                                    ->weight('medium')
                                                    ->color('success')
                                                    ->extraAttributes(function ($state): array {
                                                        $content = $state ?: 'Sin medicamentos especificados';
                                                        $lineCount = substr_count($content, "\n") + 1;

                                                        // Calcular altura dinámica
                                                        $minHeight = 50;
                                                        $lineHeight = 22;
                                                        $maxHeight = 150;

                                                        $estimatedHeight = max($minHeight, min($maxHeight, $lineCount * $lineHeight + 20));
                                                        $needsScroll = ($lineCount * $lineHeight + 20) > $maxHeight;

                                                        return [
                                                            'style' => "white-space: pre-line; word-wrap: break-word; line-height: 1.5; " .
                                                                      ($needsScroll ? "max-height: {$maxHeight}px; overflow-y: auto;" : "min-height: {$estimatedHeight}px;") .
                                                                      " padding: 10px; border-radius: 6px; border: 1px solid;",
                                                            'class' => 'bg-green-50 border-green-200 dark:bg-green-900 dark:border-green-600'
                                                        ];
                                                    })
                                                    ->copyable(),

                                                Infolists\Components\TextEntry::make('indicaciones')
                                                    ->label('📋 Indicaciones')
                                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin indicaciones especificadas')
                                                    ->color('info')
                                                    ->extraAttributes(function ($state): array {
                                                        $content = $state ?: 'Sin indicaciones especificadas';
                                                        $lineCount = substr_count($content, "\n") + 1;

                                                        // Calcular altura dinámica
                                                        $minHeight = 50;
                                                        $lineHeight = 22;
                                                        $maxHeight = 150;

                                                        $estimatedHeight = max($minHeight, min($maxHeight, $lineCount * $lineHeight + 20));
                                                        $needsScroll = ($lineCount * $lineHeight + 20) > $maxHeight;

                                                        return [
                                                            'style' => "white-space: pre-line; word-wrap: break-word; line-height: 1.5; " .
                                                                      ($needsScroll ? "max-height: {$maxHeight}px; overflow-y: auto;" : "min-height: {$estimatedHeight}px;") .
                                                                      " padding: 10px; border-radius: 6px; border: 1px solid;",
                                                            'class' => 'bg-blue-50 border-blue-200 dark:bg-blue-900 dark:border-blue-600'
                                                        ];
                                                    })
                                                    ->copyable(),
                                            ]),
                                    ])
                                    ->extraAttributes([
                                        'style' => 'border: 2px solid; border-radius: 12px; padding: 20px; margin-bottom: 16px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(16, 185, 129, 0.1));',
                                        'class' => 'border-indigo-200 dark:border-indigo-600 shadow-lg hover:shadow-xl transition-all duration-300'
                                    ])
                                    ->headerActions([])
                                    ->compact(false),
                            ])
                            ->columnSpanFull()
                            ->visible(function (Consulta $record): bool {
                                return $record->recetas()->exists();
                            }),

                        // Mensaje cuando no hay recetas
                        Infolists\Components\TextEntry::make('no_recetas')
                            ->label('')
                            ->state(' No hay recetas médicas asociadas a esta consulta')
                            ->color('gray')
                            ->weight('medium')
                            ->extraAttributes([
                                'style' => 'text-align: center; padding: 40px; border: 2px dashed; border-radius: 12px; background: linear-gradient(135deg, rgba(156, 163, 175, 0.1), rgba(209, 213, 219, 0.1));',
                                'class' => 'border-gray-300 dark:border-gray-600'
                            ])
                            ->visible(function (Consulta $record): bool {
                                return !$record->recetas()->exists();
                            }),

                        // Botones de acción generales para las recetas
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

                            Infolists\Components\Actions\Action::make('imprimir_todas_recetas')
                                ->label('Imprimir Todas las Recetas')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->action(function (Consulta $record) {
                                    // Redirigir a una vista de impresión con todas las recetas de la consulta
                                    return redirect()->route('recetas.imprimir.consulta', ['consulta' => $record->id]);
                                })
                                ->visible(function (Consulta $record) {
                                    return $record->recetas()->count() > 1; // Solo mostrar si hay más de 1 receta
                                }),
                        ])
                        ->columnSpanFull()
                        ->visible(function (Consulta $record) {
                            return $record->recetas()->count() > 0;
                        }),
                    ])
                    ->description('Lista organizada de todas las recetas médicas emitidas durante esta consulta')
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-clipboard-document-list'),
            ]);
    }
}
