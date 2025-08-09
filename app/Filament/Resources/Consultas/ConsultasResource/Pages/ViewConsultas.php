<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Filament\Resources\Facturas\FacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Consultas\Widgets\FacturacionStatus;
use App\Models\Consulta;
use App\Models\Receta;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewConsultas extends ViewRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // En ViewConsultas.php agregar botón:
            Actions\Action::make('agregar_servicios')
                ->label('Generar Cobro')
                ->icon('heroicon-o-plus-circle')
                ->color('info')
                ->url(fn () => "/admin/consultas/consultas/{$this->record->id}/servicios")
                ->visible(fn () => !$this->record->facturas()->exists()),
                        
            

            // ---- BOTÓN VER FACTURA ---------------------------------
            Actions\Action::make('ver_factura')
                ->label('Ver Factura')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) =>
                    FacturasResource::getUrl('view', [
                        'record' => $record->facturas()->first()?->id,
                    ])
                )
                ->visible(fn ($record) => $record->facturas()->exists()),
    
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

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí podrías agregar widgets si necesitas mostrar información adicional
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widget personalizado para mostrar el estado de facturación
            \App\Filament\Resources\Consultas\Widgets\FacturacionStatus::class,
            
        ];
    }

    private function getServiciosSubtotal($record): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $record->id)
            ->whereNull('factura_id')
            ->sum('subtotal');
    }

    private function getServiciosImpuesto($record): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $record->id)
            ->whereNull('factura_id')
            ->sum('impuesto_monto');
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
                        Infolists\Components\TextEntry::make('recetas')
                            ->label('')
                            ->state(function (Consulta $record): string {
                                if (!$record->recetas()->exists()) {
                                    return '<div class="flex items-center justify-center p-6 space-y-4">
                                        <div class="text-center">
                                            <div class="text-4xl mb-4">📝</div>
                                            <p class="text-gray-500 dark:text-gray-400">No hay recetas médicas asociadas a esta consulta</p>
                                        </div>
                                    </div>';
                                }
                                $html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-800">
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Medicamentos</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Indicaciones</th>
                                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">';
                                foreach ($record->recetas as $index => $receta) {
                                    $fecha = \Carbon\Carbon::parse($receta->fecha_receta)->format('d/m/Y');
                                    $recetaNum = $index + 1;
                                    $html .= '<tr class="hover:bg-blue-100 dark:hover:bg-blue-900">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">' . $recetaNum . '</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . $fecha . '</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">' . nl2br(e($receta->medicamentos)) . '</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">' . nl2br(e($receta->indicaciones)) . '</td>
                                        <td class="px-4 py-3 text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                <a href="' . route('recetas.imprimir', $receta) . '" target="_blank" class="inline-flex items-center px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors" title="Imprimir">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    Imprimir
                                                </a>
                                                <a href="' . \App\Filament\Resources\Receta\RecetaResource::getUrl('edit', ['record' => $receta->id]) . '" class="inline-flex items-center px-2 py-1 bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded transition-colors" title="Editar">
                                                    ✏️ Editar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>';
                                }
                                $html .= '</tbody></table></div>';
                                return $html;
                            })
                            ->html()
                            ->columnSpanFull(),

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
