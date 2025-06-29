<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Models\Persona;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class EditPacientes extends EditRecord
{
    protected static string $resource = PacientesResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los datos de la persona relacionada al formulario
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Separar datos de persona y paciente
        $personaData = [
            'primer_nombre' => $data['primer_nombre'],
            'segundo_nombre' => $data['segundo_nombre'] ?? null,
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'] ?? null,
            'dni' => $data['dni'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'sexo' => $data['sexo'],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'nacionalidad_id' => $data['nacionalidad_id'] ?? null,
            'updated_by' => Auth::id(),
        ];

        $pacienteData = [
            'grupo_sanguineo' => $data['grupo_sanguineo'] ?? null,
            'contacto_emergencia' => $data['contacto_emergencia'] ?? null,
        ];

        // Verificar si el DNI cambió y ya existe en otra persona
        if ($record->persona->dni !== $personaData['dni']) {
            $existePersona = Persona::where('dni', $personaData['dni'])
                                  ->where('id', '!=', $record->persona->id)
                                  ->first();
            
            if ($existePersona) {
                throw new \Exception('Ya existe otra persona con este DNI.');
            }
        }

        // Actualizar la persona
        $record->persona->update($personaData);

        // Actualizar el paciente
        $record->update($pacienteData);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Paciente actualizado exitosamente';
    }
}