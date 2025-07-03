<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;



class CreateConsultas extends CreateRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Consulta creada')
            ->body('La consulta ha sido registrada exitosamente.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return $data;
    }

    protected function afterCreate(): void
    {

        Log::info('Nueva consulta creada', [
            'consulta_id' => $this->record->id,
            'paciente_id' => $this->record->paciente_id,
            'medico_id' => $this->record->medico_id,
            'created_by' => $this->record->id(),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Crear Consulta'),

            $this->getCreateAnotherFormAction()
                ->label('Crear y Agregar Otra'),

            $this->getCancelFormAction()
                ->label('Cancelar'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver al Listado')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }
}
