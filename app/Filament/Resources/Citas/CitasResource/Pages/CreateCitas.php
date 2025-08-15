<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Filament\Notifications\Notification;

class CreateCitas extends CreateRecord
{
    protected static string $resource = CitasResource::class;

    public ?array $defaultData = null;

    public function mount(): void
    {
        // Verificar si el usuario tiene permisos para crear citas
        if (!Gate::allows('create', \App\Models\Citas::class)) {
            Notification::make()
                ->title('Sin permisos')
                ->body('No tienes permisos para crear citas.')
                ->danger()
                ->send();
            
            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }

        $user = Auth::user();
        $defaultData = [];

        // Si es médico, asignar automáticamente su ID
        if ($user && $user->roles->contains('name', 'medico') && $user->medico) {
            $defaultData['medico_id'] = $user->medico->id;
        }

        // Si viene una fecha desde el calendario, establecer datos por defecto
        if (request()->has('fecha')) {
            $fechaDesdeCalendario = request()->get('fecha');
            
            try {
                $fechaFormateada = \Carbon\Carbon::parse($fechaDesdeCalendario)->format('Y-m-d');
                $defaultData['fecha'] = $fechaFormateada;
                $defaultData['hora'] = '09:00';
            } catch (\Exception $e) {
                // Error al parsear fecha
            }
        }

        // Siempre establecer estado por defecto
        $defaultData['estado'] = 'Pendiente';

        $this->defaultData = $defaultData;
        
        parent::mount();
        
        // Intentar rellenar el formulario después del mount
        if (!empty($this->defaultData)) {
            $this->form->fill($this->defaultData);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // IMPORTANTE: Si es médico, asegurar que el medico_id sea el suyo
        if ($user && $user->roles->contains('name', 'medico') && $user->medico) {
            $data['medico_id'] = $user->medico->id;
        }

        // Verificar que medico_id esté presente
        if (!isset($data['medico_id']) || empty($data['medico_id'])) {
            throw new \Exception('No se pudo determinar el médico para la cita');
        }
        
        // Asegurar que siempre tenga estado por defecto
        if (!isset($data['estado']) || empty($data['estado'])) {
            $data['estado'] = 'Pendiente';
        }

        // Asegurar que tenga centro_id
        if (!isset($data['centro_id']) || empty($data['centro_id'])) {
            // Obtener centro_id del médico
            $medico = \App\Models\Medico::find($data['medico_id']);
            if ($medico) {
                $data['centro_id'] = $medico->centro_id;
            } else {
                $data['centro_id'] = session('current_centro_id');
            }
        }

        // Asegurar formato correcto de fecha
        if (isset($data['fecha'])) {
            $data['fecha'] = \Carbon\Carbon::parse($data['fecha'])->format('Y-m-d');
        }

        // Asegurar formato correcto de hora
        if (isset($data['hora'])) {
            if (strlen($data['hora']) === 5) {
                $data['hora'] = $data['hora'] . ':00';
            } else {
                $data['hora'] = \Carbon\Carbon::parse($data['hora'])->format('H:i:s');
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}