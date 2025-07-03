<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Medico;

class EditMedico extends EditRecord
{
    protected static string $resource = MedicoResource::class;
    protected static ?string $title = 'Editar Médico';

    protected function resolveRecord(int | string $key): Medico
    {
        // Cargar explícitamente el médico con sus relaciones
        return Medico::with(['persona', 'especialidades'])
            ->findOrFail($key);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Verificación adicional de que el registro está cargado correctamente
        if (!is_object($this->record)) {
            abort(500, 'El registro médico no se cargó correctamente');
        }

        // Cargar relaciones si no están cargadas
        $this->record->loadMissing(['persona', 'especialidades']);

        return array_merge($data, [
            'primer_nombre' => $this->record->persona->primer_nombre ?? null,
            'segundo_nombre' => $this->record->persona->segundo_nombre ?? null,
            'primer_apellido' => $this->record->persona->primer_apellido ?? null,
            'segundo_apellido' => $this->record->persona->segundo_apellido ?? null,
            'dni' => $this->record->persona->dni ?? null,
            'telefono' => $this->record->persona->telefono ?? null,
            'direccion' => $this->record->persona->direccion ?? null,
            'sexo' => $this->record->persona->sexo ?? null,
            'fecha_nacimiento' => $this->record->persona->fecha_nacimiento ?? null,
            'nacionalidad_id' => $this->record->persona->nacionalidad_id ?? null,
            'especialidades' => $this->record->especialidades->pluck('id')->toArray(),
        ]);
    }

    protected function handleRecordUpdate($record, array $data): Medico
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
        ]);

        // 2. Actualizar número de colegiación
        $record->update([
            'numero_colegiacion' => $data['numero_colegiacion']
        ]);

       

        // 3. Sincronizar especialidades (con verificación explícita)
        if (array_key_exists('especialidades', $data)) {
            $record->especialidades()->sync($data['especialidades']);
        } else {
            // Si no vienen especialidades, limpiar las existentes
            //$record->especialidades()->detach();
        }

        DB::commit();
        return $record;

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al actualizar médico: '.$e->getMessage());
        throw $e;
    }
}
    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->icon('heroicon-o-check')
                ->color('primary'),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->success()
            ->title('Médico actualizado')
            ->body('Los datos del médico y sus especialidades se han actualizado correctamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}