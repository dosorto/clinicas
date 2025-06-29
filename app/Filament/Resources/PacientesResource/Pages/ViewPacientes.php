<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPacientes extends ViewRecord
{
    protected static string $resource = PacientesResource::class;

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
}
