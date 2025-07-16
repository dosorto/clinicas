<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use App\Models\Persona;
use App\Models\Medico;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;

class CreateMedico extends CreateRecord
{
    protected static string $resource = MedicoResource::class;
    protected static ?string $title = 'Crear Médico';

protected function handleRecordCreation(array $data): Medico
{
    // Primero creamos o actualizamos la persona
    $persona = Persona::updateOrCreate(
        ['dni' => $data['dni']],
        [
            'primer_nombre' => $data['primer_nombre'],
            'segundo_nombre' => $data['segundo_nombre'] ?? null,
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'sexo' => $data['sexo'],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'nacionalidad_id' => $data['nacionalidad_id'] ?? null,
        ]
    );

    // Luego creamos el médico asociado (incluyendo los horarios)
    return Medico::create([
        'persona_id' => $persona->id,
        'numero_colegiacion' => $data['numero_colegiacion'],
        'horario_entrada' => $data['horario_entrada'],  // Añade esta línea
        'horario_salida' => $data['horario_salida']     // Añade esta línea
    ]);
}
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear Médico')
                ->submit('create')
                ->icon('heroicon-o-user-plus')
                ->color('primary'),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-x-mark')
                ->color('danger')
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Médico creado exitosamente';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title($this->getCreatedNotificationTitle())
            ->body('El médico y sus datos personales han sido registrados correctamente.');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}