<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewPacientes extends ViewRecord
{
    protected static string $resource = PacientesResource::class;

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Resources\Pacientes\PacientesResource\RelationManagers\ConsultasRelationManager::class,
        ];
    }

    public function getTitle(): string
    {
        return "Información del Paciente - {$this->record->persona->nombre_completo}";
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $paciente = $this->record;
        $totalConsultas = \App\Models\Consulta::where('paciente_id', $paciente->id)->count();
        $ultimaConsulta = \App\Models\Consulta::where('paciente_id', $paciente->id)->latest()->first();
        $medicosAtendieron = \App\Models\Consulta::where('paciente_id', $paciente->id)->distinct('medico_id')->count();
        
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Datos Personales')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\ImageEntry::make('persona.foto')
                                    ->label('Fotografía')
                                    ->circular()
                                    ->size(120)
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('persona.dni')
                                    ->label('DNI/Cédula')
                                    ->weight('bold')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('persona.nacionalidad.nacionalidad')
                                    ->label('Nacionalidad'),

                                Infolists\Components\TextEntry::make('persona.primer_nombre')
                                    ->label('Primer Nombre'),

                                Infolists\Components\TextEntry::make('persona.segundo_nombre')
                                    ->label('Segundo Nombre')
                                    ->placeholder('No registrado'),

                                Infolists\Components\TextEntry::make('persona.primer_apellido')
                                    ->label('Primer Apellido'),

                                Infolists\Components\TextEntry::make('persona.segundo_apellido')
                                    ->label('Segundo Apellido')
                                    ->placeholder('No registrado'),

                                Infolists\Components\TextEntry::make('persona.telefono')
                                    ->label('Teléfono')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('persona.sexo')
                                    ->label('Sexo')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'M' => 'Masculino',
                                        'F' => 'Femenino',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'M' => 'info',
                                        'F' => 'warning',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->date('d/m/Y'),

                                Infolists\Components\TextEntry::make('persona.direccion')
                                    ->label('Dirección')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Infolists\Components\Section::make('Datos del Paciente')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('grupo_sanguineo')
                                    ->label('Grupo Sanguíneo')
                                    ->badge()
                                    ->color('danger'),

                                Infolists\Components\TextEntry::make('contacto_emergencia')
                                    ->label('Contacto de Emergencia')
                                    ->copyable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Infolists\Components\Section::make('Enfermedades')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('enfermedades')
                            ->schema([
                                Infolists\Components\TextEntry::make('nombre')
                                    ->label('Enfermedad'),
                                Infolists\Components\TextEntry::make('descripcion')
                                    ->label('Descripción')
                                    ->limit(100),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->placeholder('Sin enfermedades registradas'),
                    ])
                    ->collapsible()
                    ->collapsed(true),

                Infolists\Components\Section::make('Estadísticas Médicas')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_consultas')
                                    ->label('Total de Consultas')
                                    ->state($totalConsultas)
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('ultima_consulta')
                                    ->label('Última Consulta')
                                    ->state($ultimaConsulta ? $ultimaConsulta->created_at->format('d/m/Y') : 'Sin consultas')
                                    ->badge()
                                    ->color('success'),

                                Infolists\Components\TextEntry::make('medicos_atendieron')
                                    ->label('Médicos que lo han atendido')
                                    ->state($medicosAtendieron)
                                    ->badge()
                                    ->color('warning'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Infolists\Components\Section::make('Información del Sistema')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->dateTime('d/m/Y H:i'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ]);
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
            $data['foto'] = $persona->foto;
        }

        // Obtener la primera enfermedad del paciente (si existe)
        if ($this->record->enfermedades->isNotEmpty()) {
            $enfermedad = $this->record->enfermedades->first();
            $pivot = $enfermedad->pivot;
            
            $data['enfermedad_id'] = $enfermedad->id;
            $data['fecha_diagnostico'] = $pivot->fecha_diagnostico;
            $data['tratamiento'] = $pivot->tratamiento;
        }

        return $data;
    }
}