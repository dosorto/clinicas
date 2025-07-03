<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Models\Pacientes;
use App\Models\Persona;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreatePacientes extends CreateRecord
{
    protected static string $resource = PacientesResource::class;

    protected function handleRecordCreation(array $data): Pacientes
    {
        DB::beginTransaction();

        try {
            // 1. Crear o actualizar persona
            $personaData = [
                'primer_nombre' => $data['primer_nombre'],
                'segundo_nombre' => $data['segundo_nombre'],
                'primer_apellido' => $data['primer_apellido'],
                'segundo_apellido' => $data['segundo_apellido'],
                'dni' => $data['dni'],
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'],
                'sexo' => $data['sexo'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'nacionalidad_id' => $data['nacionalidad_id'],
                'foto' => $data['foto'] ?? null,
            ];

            $persona = Persona::updateOrCreate(
                ['dni' => $data['dni']],
                $personaData
            );

            // 2. Crear paciente
            $paciente = Pacientes::create([
                'persona_id' => $persona->id,
                'grupo_sanguineo' => $data['grupo_sanguineo'],
                'contacto_emergencia' => $data['contacto_emergencia'],
            ]);

            // 3. Agregar enfermedad si existe - CORRECCIÓN AQUÍ
            if (isset($data['enfermedad_id'])) {
                $paciente->enfermedades()->attach($data['enfermedad_id'], [
                    'fecha_diagnostico' => $data['fecha_diagnostico'],
                    'tratamiento' => $data['tratamiento'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return $paciente;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}