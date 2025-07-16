<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Actions;

class ViewPacientes extends ViewRecord
{
    protected static string $resource = PacientesResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header con foto y datos básicos
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(1)
                                ->schema([
                                    Infolists\Components\ImageEntry::make('persona.fotografia')
                                        ->label('')
                                        ->circular()
                                        ->size(180)
                                        ->getStateUsing(function ($record) {
                                            if ($record->persona->fotografia) {
                                                return asset('storage/' . $record->persona->fotografia);
                                            }
                                            return PacientesResource::generateAvatar(
                                                $record->persona->primer_nombre,
                                                $record->persona->primer_apellido
                                            );
                                        }),
                                ])
                                ->columnSpan(1),
                            
                            Infolists\Components\Grid::make(1)
                                ->schema([
                                    Infolists\Components\TextEntry::make('nombre_completo')
                                        ->label('')
                                        ->getStateUsing(function ($record) {
                                            return trim("{$record->persona->primer_nombre} {$record->persona->segundo_nombre} {$record->persona->primer_apellido} {$record->persona->segundo_apellido}");
                                        })
                                        ->weight(FontWeight::Bold)
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    
                                    Infolists\Components\TextEntry::make('persona.dni')
                                        ->label('DNI/Cédula')
                                        ->icon('heroicon-m-identification')
                                        ->iconPosition(IconPosition::Before)
                                        ->copyable()
                                        ->copyMessage('DNI copiado')
                                        ->weight(FontWeight::Medium),
                                    
                                    Infolists\Components\TextEntry::make('grupo_sanguineo')
                                        ->label('Grupo Sanguíneo')
                                        ->icon('heroicon-m-heart')
                                        ->iconPosition(IconPosition::Before)
                                        ->badge()
                                        ->color('danger')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Medium),
                                    
                                    Infolists\Components\TextEntry::make('edad')
                                        ->label('Edad')
                                        ->icon('heroicon-m-calendar-days')
                                        ->iconPosition(IconPosition::Before)
                                        ->getStateUsing(function ($record) {
                                            if ($record->persona->fecha_nacimiento) {
                                                $edad = \Carbon\Carbon::parse($record->persona->fecha_nacimiento)->age;
                                                return $edad . ' años';
                                            }
                                            return 'No especificada';
                                        })
                                        ->weight(FontWeight::Medium),
                                    
                                    Infolists\Components\TextEntry::make('persona.sexo')
                                        ->label('Sexo')
                                        ->icon('heroicon-m-user')
                                        ->iconPosition(IconPosition::Before)
                                        ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculino' : 'Femenino')
                                        ->badge()
                                        ->color(fn ($state) => $state === 'M' ? 'info' : 'success'),
                                ])
                                ->columnSpan(2),
                        ])
                        ->from('md'),
                    ])
                    ->headerActions([
                        // ✅ CAMBIO: Usar Action de Infolists en lugar de EditAction
                        Infolists\Components\Actions\Action::make('edit')
                            ->label('Editar Paciente')
                            ->icon('heroicon-m-pencil')
                            ->url(fn ($record) => static::getResource()::getUrl('edit', ['record' => $record])),
                    ])
                    ->compact(),

                // Información Personal
                Infolists\Components\Section::make('Información Personal')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->icon('heroicon-m-calendar')
                                    ->iconPosition(IconPosition::Before)
                                    ->date('d/m/Y')
                                    ->weight(FontWeight::Medium),
                                
                                Infolists\Components\TextEntry::make('persona.nacionalidad.nacionalidad')
                                    ->label('Nacionalidad')
                                    ->icon('heroicon-m-flag')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('persona.telefono')
                                    ->label('Teléfono')
                                    ->icon('heroicon-m-phone')
                                    ->iconPosition(IconPosition::Before)
                                    ->copyable()
                                    ->copyMessage('Teléfono copiado')
                                    ->weight(FontWeight::Medium),
                            ]),
                        
                        Infolists\Components\TextEntry::make('persona.direccion')
                            ->label('Dirección')
                            ->icon('heroicon-m-map-pin')
                            ->iconPosition(IconPosition::Before)
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-m-user-circle')
                    ->collapsible(),

                // Información Médica
                Infolists\Components\Section::make('Información Médica')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('grupo_sanguineo')
                                    ->label('Grupo Sanguíneo')
                                    ->icon('heroicon-m-heart')
                                    ->iconPosition(IconPosition::Before)
                                    ->badge()
                                    ->color('danger')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Medium),
                                
                                Infolists\Components\TextEntry::make('contacto_emergencia')
                                    ->label('Contacto de Emergencia')
                                    ->icon('heroicon-m-phone-arrow-up-right')
                                    ->iconPosition(IconPosition::Before)
                                    ->copyable()
                                    ->copyMessage('Contacto de emergencia copiado')
                                    ->weight(FontWeight::Medium),
                            ]),
                        
                        Infolists\Components\TextEntry::make('total_enfermedades')
                            ->label('Total de Enfermedades')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->iconPosition(IconPosition::Before)
                            ->getStateUsing(function ($record) {
                                $total = $record->enfermedades->count();
                                return $total . ' ' . ($total === 1 ? 'enfermedad' : 'enfermedades');
                            })
                            ->badge()
                            ->color(function ($record) {
                                $total = $record->enfermedades->count();
                                if ($total === 0) return 'success';
                                if ($total <= 2) return 'warning';
                                return 'danger';
                            }),
                    ])
                    ->icon('heroicon-m-heart')
                    ->collapsible(),

                // Historial de Enfermedades
                Infolists\Components\Section::make('Historial de Enfermedades')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('enfermedades')
                            ->schema([
                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\Split::make([
                                            Infolists\Components\TextEntry::make('enfermedades')
                                                ->label('')
                                                ->weight(FontWeight::Bold)
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::Medium)
                                                ->icon('heroicon-m-exclamation-triangle')
                                                ->iconPosition(IconPosition::Before)
                                                ->color('danger'),
                                            
                                            Infolists\Components\TextEntry::make('pivot.fecha_diagnostico')
                                                ->label('')
                                                ->formatStateUsing(function ($state) {
                                                    if ($state) {
                                                        $year = date('Y', strtotime($state));
                                                        return "Diagnosticado en {$year}";
                                                    }
                                                    return 'Fecha no especificada';
                                                })
                                                ->badge()
                                                ->color('info')
                                                ->icon('heroicon-m-calendar')
                                                ->iconPosition(IconPosition::Before),
                                        ]),
                                        
                                        Infolists\Components\TextEntry::make('pivot.tratamiento')
                                            ->label('Tratamiento')
                                            ->icon('heroicon-m-clipboard-document')
                                            ->iconPosition(IconPosition::Before)
                                            ->formatStateUsing(fn ($state) => $state ?: 'No especificado')
                                            ->columnSpanFull()
                                            ->prose(),
                                    ]),
                            ])
                            ->contained(true),
                    ])
                    ->icon('heroicon-m-clipboard-document-list')
                    ->collapsible()
                    ->hidden(fn ($record) => $record->enfermedades->isEmpty()),

                // Mensaje cuando no hay enfermedades
                Infolists\Components\Section::make('Historial de Enfermedades')
                    ->schema([
                        Infolists\Components\TextEntry::make('sin_enfermedades')
                            ->label('')
                            ->getStateUsing(fn () => 'Este paciente no tiene enfermedades registradas.')
                            ->icon('heroicon-m-check-circle')
                            ->iconPosition(IconPosition::Before)
                            ->color('success')
                            ->weight(FontWeight::Medium),
                    ])
                    ->icon('heroicon-m-heart')
                    ->collapsible()
                    ->visible(fn ($record) => $record->enfermedades->isEmpty()),

                // Información del Sistema
                Infolists\Components\Section::make('Información del Sistema')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->icon('heroicon-m-plus-circle')
                                    ->iconPosition(IconPosition::Before)
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Medium),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->icon('heroicon-m-pencil-square')
                                    ->iconPosition(IconPosition::Before)
                                    ->dateTime('d/m/Y H:i')
                                    ->weight(FontWeight::Medium),
                            ]),
                    ])
                    ->icon('heroicon-m-cog-6-tooth')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Paciente')
                ->icon('heroicon-m-pencil'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $persona = $this->record->persona;
        
        if ($persona) {
            $data['primer_nombre'] = $persona->primer_nombre;
            $data['segundo_nombre'] = $persona->segundo_nombre;
            $data['primer_apellido'] = $persona->primer_apellido;
            $data['segundo_apellido'] = $persona->segundo_apellido;
            $data['dni'] = $persona->dni;
            $data['telefono'] = $persona->telefono;
            $data['direccion'] = $persona->direccion;
            $data['sexo'] = $persona->sexo;
            $data['fecha_nacimiento'] = $persona->fecha_nacimiento;
            $data['nacionalidad_id'] = $persona->nacionalidad_id;
            $data['fotografia'] = $persona->fotografia;
        }

        // Obtener TODAS las enfermedades del paciente para la vista
        $enfermedadesData = [];
        if ($this->record->enfermedades->isNotEmpty()) {
            foreach ($this->record->enfermedades as $enfermedad) {
                $pivot = $enfermedad->pivot;
                
                // Extraer el año de la fecha de diagnóstico
                $anoDiagnostico = $pivot->fecha_diagnostico ? 
                    date('Y', strtotime($pivot->fecha_diagnostico)) : 
                    date('Y');
                
                $enfermedadesData[] = [
                    'enfermedad_id' => $enfermedad->id,
                    'ano_diagnostico' => $anoDiagnostico,
                    'tratamiento' => $pivot->tratamiento,
                ];
            }
        }
        
        $data['enfermedades_data'] = $enfermedadesData;

        return $data;
    }
}