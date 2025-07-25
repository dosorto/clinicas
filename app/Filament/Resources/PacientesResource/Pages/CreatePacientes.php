<?php

namespace App\Filament\Resources\PacientesResource\Pages;

use App\Filament\Resources\PacientesResource;
use App\Models\Pacientes;
use App\Models\Persona;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreatePacientes extends CreateRecord
{
    protected static string $resource = PacientesResource::class;

    protected function handleRecordCreation(array $data): Pacientes
    {
        DB::beginTransaction();

        try {
            // Validar duplicados de enfermedades antes de crear
            if (isset($data['enfermedades_data']) && is_array($data['enfermedades_data'])) {
                $enfermedadesSeleccionadas = array_filter(
                    array_column($data['enfermedades_data'], 'enfermedad_id'),
                    fn($id) => !is_null($id)
                );
                
                if (count($enfermedadesSeleccionadas) !== count(array_unique($enfermedadesSeleccionadas))) {
                    throw new \Exception('No puede seleccionar la misma enfermedad más de una vez.');
                }
            }

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
                'fotografia' => $data['fotografia'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            $persona = Persona::updateOrCreate(
                ['dni' => $data['dni']],
                $personaData
            );

            // 2. Verificar si ya existe un paciente con esta persona
            $pacienteExistente = Pacientes::where('persona_id', $persona->id)->first();
            if ($pacienteExistente) {
                throw new \Exception('Ya existe un paciente registrado con este DNI.');
            }

            // 3. Crear paciente
            $paciente = Pacientes::create([
                'persona_id' => $persona->id,
                'grupo_sanguineo' => $data['grupo_sanguineo'],
                'contacto_emergencia' => $data['contacto_emergencia'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // 4. Agregar enfermedades sin duplicados
            if (isset($data['enfermedades_data']) && is_array($data['enfermedades_data'])) {
                $enfermedadesSeleccionadas = [];
                
                foreach ($data['enfermedades_data'] as $enfermedadData) {
                    if (isset($enfermedadData['enfermedad_id'])) {
                        // Verificar duplicados
                        if (in_array($enfermedadData['enfermedad_id'], $enfermedadesSeleccionadas)) {
                            continue; // Saltar enfermedad duplicada
                        }
                        
                        $enfermedadesSeleccionadas[] = $enfermedadData['enfermedad_id'];
                        
                        // Convertir año a fecha completa (1 de enero del año)
                        $fechaDiagnostico = $enfermedadData['ano_diagnostico'] . '-01-01';
                        
                        $paciente->enfermedades()->attach($enfermedadData['enfermedad_id'], [
                            'fecha_diagnostico' => $fechaDiagnostico,
                            'tratamiento' => $enfermedadData['tratamiento'] ?? null,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            
            // Notificación de éxito
            Notification::make()
                ->title('Paciente creado exitosamente')
                ->body("El paciente {$persona->primer_nombre} {$persona->primer_apellido} ha sido registrado correctamente.")
                ->success()
                ->send();
            
            return $paciente;

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Notificación de error
            Notification::make()
                ->title('Error al crear paciente')
                ->body($e->getMessage())
                ->danger()
                ->send();
            
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Paciente creado exitosamente';
    }
}