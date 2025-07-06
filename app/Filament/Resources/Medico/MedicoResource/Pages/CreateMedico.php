<?php
/*
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

        // Luego creamos el médico asociado
        return Medico::create([
            'persona_id' => $persona->id,
            'numero_colegiacion' => $data['numero_colegiacion']
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
                ->color('gray')
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
}*/

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use App\Models\Persona;
use Filament\Actions;
use App\Models\Medico;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateMedico extends CreateRecord
{
    protected static string $resource = MedicoResource::class;

    protected static ?string $title = 'Crear Médico';



        public static function canCreateAnother(): bool
    {
        return false; // Evita creación automática si lo necesitas
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si quieres manipular datos antes de crear
        return $data;
    }



    protected function handleRecordCreation(array $data): Medico
    {
        DB::beginTransaction();

        try {
            // 1. Crear la persona primero
            $persona = Persona::create([
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
            ]);

            // 2. Crear el médico con el persona_id
            $medico = Medico::create([
                'numero_colegiacion' => $data['numero_colegiacion'],
                'persona_id' => $persona->id, // Asegurar que persona_id está asignado
            ]);

            // 3. Asignar especialidades
            if (isset($data['especialidades'])) {
                $medico->especialidades()->sync($data['especialidades']);
            }

            DB::commit();
            return $medico;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Crear')
                ->icon('heroicon-o-check')
                ->color('primary'),
                
            Actions\Action::make('back')
                ->label('Cancelar')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->url($this->getResource()::getUrl('index')),
                
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Médico creado')
            ->body('El médico se ha registrado correctamente.');
    }


    protected function getHeaderActions(): array
    {
        return [];
    }

}