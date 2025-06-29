<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Models\Pacientes;
use App\Models\Persona;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePacientes extends CreateRecord
{
    protected static string $resource = PacientesResource::class;

    protected function handleRecordCreation(array $data): Pacientes
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
            'created_by' => 1// Agregar el ID del usuario autenticado
        ];

        $pacienteData = [
            'grupo_sanguineo' => $data['grupo_sanguineo'] ?? null,
            'contacto_emergencia' => $data['contacto_emergencia'] ?? null,
        ];

        // Verificar si ya existe persona por DNI
        $persona = Persona::where('dni', $personaData['dni'])->first();
        
        if ($persona) {
            // Persona existe, mostrar notificación
            Notification::make()
                ->title('Persona encontrada')
                ->body("Se usó registro existente: {$persona->nombre_completo}")
                ->success()
                ->send();
        } else {
            // Crear nueva persona
            $persona = Persona::create($personaData);
            
            Notification::make()
                ->title('Nueva persona creada')
                ->body("Se creó nuevo registro: {$persona->nombre_completo}")
                ->success()
                ->send();
        }

        // Verificar si ya existe un paciente para esta persona
        $existingPaciente = Pacientes::where('persona_id', $persona->id)->first();
        
        if ($existingPaciente) {
            // Actualizar paciente existente
            $existingPaciente->update($pacienteData);
            
            Notification::make()
                ->title('Paciente actualizado')
                ->body('Se actualizaron los datos del paciente existente')
                ->warning()
                ->send();
                
            return $existingPaciente;
        }

        // Crear nuevo paciente
        $pacienteData['persona_id'] = $persona->id;
        $paciente = Pacientes::create($pacienteData);
        
        Notification::make()
            ->title('Paciente creado exitosamente')
            ->body('Se creó un nuevo paciente')
            ->success()
            ->send();

        return $paciente;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}