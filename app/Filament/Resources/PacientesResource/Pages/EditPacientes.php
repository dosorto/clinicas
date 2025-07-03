<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Models\Persona;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class EditPacientes extends EditRecord
{
    protected static string $resource = PacientesResource::class;

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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::beginTransaction();

        try {
            // 1. Actualizar datos de la persona
            $record->persona->update([
                'primer_nombre' => $data['primer_nombre'],
                'segundo_nombre' => $data['segundo_nombre'],
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'],
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'],
                'sexo' => $data['sexo'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'nacionalidad_id' => $data['nacionalidad_id'],
                'foto' => $data['foto'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            // 2. Actualizar el paciente
            $record->update([
                'grupo_sanguineo' => $data['grupo_sanguineo'],
                'contacto_emergencia' => $data['contacto_emergencia'],
            ]);

            // 3. Sincronizar enfermedades - CORRECCIÓN AQUÍ
            $record->enfermedades()->detach();
            
            if (isset($data['enfermedad_id'])) {
                $record->enfermedades()->attach($data['enfermedad_id'], [
                    'fecha_diagnostico' => $data['fecha_diagnostico'],
                    'tratamiento' => $data['tratamiento'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return $record;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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