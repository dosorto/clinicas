<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCitas extends CreateRecord
{
    protected static string $resource = CitasResource::class;

    public ?array $defaultData = null;

    public function mount(): void
    {
        // Si viene una fecha desde el calendario, establecer datos por defecto
        if (request()->has('fecha')) {
            $fechaDesdeCalendario = request()->get('fecha');
            
            try {
                $fechaFormateada = \Carbon\Carbon::parse($fechaDesdeCalendario)->format('Y-m-d');
                
                $this->defaultData = [
                    'medico_id' => 21, // Usar un ID fijo por ahora para testing
                    'fecha' => $fechaFormateada,
                    'hora' => '09:00',
                    'estado' => 'Pendiente',
                ];
                
                \Log::info('Data establecida antes de mount:', $this->defaultData);
                
            } catch (\Exception $e) {
                \Log::error('Error al parsear fecha:', $e->getMessage());
            }
        }
        
        parent::mount();
        
        // Intentar rellenar el formulario después del mount
        if (!empty($this->defaultData)) {
            $this->form->fill($this->defaultData);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        \Log::info('=== INICIO mutateFormDataBeforeCreate ===');
        \Log::info('Datos recibidos:', $data);
        
        // IMPORTANTE: Asegurar que el medico_id esté presente
        if (!isset($data['medico_id']) || empty($data['medico_id'])) {
            $data['medico_id'] = 21; // Usar un ID fijo por ahora
            \Log::info('Medico ID establecido por defecto: ' . $data['medico_id']);
        }
        
        // Asegurar que siempre tenga estado por defecto
        if (!isset($data['estado']) || empty($data['estado'])) {
            $data['estado'] = 'Pendiente';
            \Log::info('Estado establecido por defecto: Pendiente');
        }

        // Asegurar formato correcto de fecha
        if (isset($data['fecha'])) {
            $fechaOriginal = $data['fecha'];
            $data['fecha'] = \Carbon\Carbon::parse($data['fecha'])->format('Y-m-d');
            \Log::info('Fecha convertida:', ['original' => $fechaOriginal, 'convertida' => $data['fecha']]);
        }

        // Asegurar formato correcto de hora
        if (isset($data['hora'])) {
            $horaOriginal = $data['hora'];
            if (strlen($data['hora']) === 5) {
                $data['hora'] = $data['hora'] . ':00';
            } else {
                $data['hora'] = \Carbon\Carbon::parse($data['hora'])->format('H:i:s');
            }
            \Log::info('Hora convertida:', ['original' => $horaOriginal, 'convertida' => $data['hora']]);
        }
        
        \Log::info('Datos finales para crear:', $data);
        \Log::info('=== FIN mutateFormDataBeforeCreate ===');
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}