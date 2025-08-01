<?php

namespace App\Filament\Resources\NominaResource\Pages;

use App\Filament\Resources\NominaResource;
use App\Models\Medico;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Actions\Action;

class ManageNominas extends ManageRecords
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_nomina_general')
                ->label('Generar nómina general')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('fecha_inicio')
                        ->label('Fecha de inicio')
                        ->required()
                        ->default(Carbon::now()->startOfMonth())
                        ->maxDate(Carbon::now()),
                    
                    \Filament\Forms\Components\DatePicker::make('fecha_fin')
                        ->label('Fecha de fin')
                        ->required()
                        ->default(Carbon::now())
                        ->maxDate(Carbon::now())
                        ->afterOrEqual('fecha_inicio'),
                        
                    \Filament\Forms\Components\Select::make('medico_id')
                        ->label('Médico (opcional)')
                        ->options(function() {
                            return Medico::all()->pluck('nombre_completo', 'id');
                        })
                        ->searchable()
                        ->placeholder('Todos los médicos'),
                        
                    \Filament\Forms\Components\Grid::make()
                        ->schema([
                            \Filament\Forms\Components\Checkbox::make('incluir_pagados')
                                ->label('Incluir pagos realizados')
                                ->default(true),
                                
                            \Filament\Forms\Components\Checkbox::make('incluir_pendientes')
                                ->label('Incluir liquidaciones pendientes')
                                ->default(false),
                        ]),
                ])
                ->action(function (array $data) {
                    // Redirigir a la URL de generación de PDF con los parámetros necesarios
                    $queryParams = http_build_query([
                        'fecha_inicio' => $data['fecha_inicio'],
                        'fecha_fin' => $data['fecha_fin'],
                        'medico_id' => $data['medico_id'] ?? null,
                        'incluir_pagados' => $data['incluir_pagados'] ?? true,
                        'incluir_pendientes' => $data['incluir_pendientes'] ?? false,
                    ]);
                    
                    return redirect()->to(route('nomina.generar.pdf') . '?' . $queryParams);
                }),
        ];
    }
}
