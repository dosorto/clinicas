<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;

class ViewMedico extends ViewRecord
{
    protected static string $resource = MedicoResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Sección de encabezado con foto y datos principales
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make(1)
                                ->schema([
                                    ImageEntry::make('persona.foto')
                                        ->hiddenLabel()
                                        ->size(150)
                                        ->getStateUsing(function ($record) {
                                            // Si existe persona y tiene foto, mostrarla directamente
                                            if ($record->persona && !empty($record->persona->foto)) {
                                                // La foto se guarda en 'personas/fotos/' según tu configuración
                                                return Storage::url($record->persona->foto);
                                            }
                                            
                                            // Generar avatar con iniciales si no hay foto
                                            $iniciales = 'DR';
                                            if ($record->persona) {
                                                $iniciales = strtoupper(substr($record->persona->primer_nombre, 0, 1));
                                                $iniciales .= strtoupper(substr($record->persona->primer_apellido, 0, 1));
                                            }
                                            
                                            return "https://ui-avatars.com/api/?name={$iniciales}&size=150&background=3b82f6&color=ffffff&bold=true&format=svg";
                                        })
                                        ->extraAttributes([
                                            'style' => '
                                                width: 150px !important; 
                                                height: 150px !important; 
                                                border-radius: 50% !important; 
                                                border: 4px solid #e5e7eb; 
                                                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
                                                object-fit: cover; 
                                                display: block; 
                                                margin: 0 auto;
                                                aspect-ratio: 1/1;
                                                overflow: hidden;
                                            '
                                        ]),
                                ])
                                ->columnSpan(1),
                            
                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('nombre_completo')
                                        ->label('')
                                        ->getStateUsing(fn ($record) => 
                                            $record->persona->primer_nombre . ' ' . 
                                            ($record->persona->segundo_nombre ? $record->persona->segundo_nombre . ' ' : '') .
                                            $record->persona->primer_apellido . ' ' . 
                                            ($record->persona->segundo_apellido ?? '')
                                        )
                                        ->weight(FontWeight::Bold)
                                        ->size('xl')
                                        ->color('primary'),
                                    
                                    TextEntry::make('especialidades_principales')
                                        ->label('Especialidades Médicas')
                                        ->getStateUsing(fn ($record) => 
                                            $record->especialidades->pluck('especialidad')->join(', ')
                                        )
                                        ->badge()
                                        ->color('success')
                                        ->separator(', '),
                                    
                                    TextEntry::make('numero_colegiacion')
                                        ->label('N° de Colegiación')
                                        ->prefix('')
                                        ->weight(FontWeight::SemiBold)
                                         ->badge()
                                        ->color('success'),
                                ])
                                ->columnSpan(2),
                        ])
                        ->from('md')
                    ])
                    ->columnSpanFull(),

                // Información Personal
                Section::make('Información Personal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('persona.dni')
                                    ->label('DNI')
                                    ->icon('heroicon-o-identification')
                                    ->copyable()
                                    ->copyMessage('DNI copiado'),
                                
                                TextEntry::make('persona.telefono')
                                    ->label('Teléfono')
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->copyMessage('Teléfono copiado'),
                                
                                TextEntry::make('persona.sexo')
                                    ->label('Sexo')
                                    ->icon('heroicon-o-user')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'M' => 'Masculino',
                                        'F' => 'Femenino',
                                        default => $state,
                                    }),
                                
                                TextEntry::make('persona.fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->icon('heroicon-o-cake')
                                    ->date('d/m/Y')
                                    ->suffix(fn ($record) => 
                                        ' (' . \Carbon\Carbon::parse($record->persona->fecha_nacimiento)->age . ' años)'
                                    ),
                                
                                TextEntry::make('persona.nacionalidad.nacionalidad')
                                    ->label('Nacionalidad')
                                    ->icon('heroicon-o-flag'),
                                
                                TextEntry::make('persona.direccion')
                                    ->label('Dirección')
                                    ->icon('heroicon-o-map-pin')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->collapsible()
                    ->icon('heroicon-o-user-circle'),

                // Información Profesional
                Section::make('Información Profesional')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('horario_entrada')
                                    ->label('Horario de Entrada')
                                    ->icon('heroicon-o-clock')
                                    ->time('g:i A')
                                    ->badge()
                                    ->color('success'),
                                
                                TextEntry::make('horario_salida')
                                    ->label('Horario de Salida')
                                    ->icon('heroicon-o-clock')
                                    ->time('g:i A')
                                    ->badge()
                                    ->color('danger'),
                                
                                TextEntry::make('duracion_jornada')
                                    ->label('Duración de Jornada')
                                    ->icon('heroicon-o-clock')
                                    ->getStateUsing(function ($record) {
                                        $entrada = \Carbon\Carbon::parse($record->horario_entrada);
                                        $salida = \Carbon\Carbon::parse($record->horario_salida);
                                        $duracion = $entrada->diffInHours($salida);
                                        return $duracion . ' horas';
                                    })
                                    ->badge()
                                    ->color('info'),
                            ])
                    ])
                    ->icon('heroicon-o-briefcase'),

             /*   // Especialidades Médicas
                Section::make('Especialidades Médicas')
                    ->schema([
                        TextEntry::make('especialidades.especialidad')
                            ->label('')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->limitList(10)
                            ->expandableLimitedList()
                            ->color('primary')
                            ->weight(FontWeight::Medium)
                    ])
                    ->icon('heroicon-o-academic-cap')
                    ->headerActions([
                        \Filament\Infolists\Components\Actions\Action::make('total_especialidades')
                            ->label(fn ($record) => 'Total: ' . $record->especialidades->count() . ' especialidades')
                            ->disabled()
                            ->color('gray')
                    ]),*/

                // Información del Sistema
                Section::make('Información del Sistema')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->icon('heroicon-o-calendar-days')
                                    ->dateTime('d/m/Y H:i')
                                    ->since(),
                                
                                TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->icon('heroicon-o-arrow-path')
                                    ->dateTime('d/m/Y H:i')
                                    ->since(),
                            ])
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los datos de la persona relacionada para visualización
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
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Editar Perfil')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->url(fn () => static::$resource::getUrl('edit', ['record' => $this->record])),
            
            Action::make('back')
                ->label('Volver al Listado')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(static::$resource::getUrl('index')),
        ];
    }

    public function getTitle(): string
    {
        $persona = $this->record->persona;
        return 'Dr(a). ' . $persona->primer_nombre . ' ' . $persona->primer_apellido;
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}