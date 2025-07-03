<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditConsultas extends EditRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Ver Consulta'),

            Actions\DeleteAction::make()
                ->label('Eliminar')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Consulta')
                ->modalDescription('¿Estás seguro de que deseas eliminar esta consulta? Esta acción se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar'),

            Actions\ForceDeleteAction::make()
                ->label('Eliminar Permanentemente')
                ->requiresConfirmation()
                ->modalHeading('Eliminar Consulta Permanentemente')
                ->modalDescription('¿Estás seguro de que deseas eliminar permanentemente esta consulta? Esta acción no se puede deshacer.')
                ->modalSubmitActionLabel('Sí, eliminar permanentemente'),

            Actions\RestoreAction::make()
                ->label('Restaurar'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Consulta actualizada')
            ->body('Los cambios han sido guardados exitosamente.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        return $data;
    }

    protected function afterSave(): void
    {
        // Lógica adicional después de guardar
        Log::info('Consulta actualizada', [
            'consulta_id' => $this->record->id,
            'updated_by' => $this->record->id(),
            'changes' => $this->record->getChanges(),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Guardar Cambios'),

            $this->getCancelFormAction()
                ->label('Cancelar'),
        ];
    }

    protected function configureDeleteAction(Actions\DeleteAction $action): void
    {
        $action
            ->after(function () {
                Notification::make()
                    ->success()
                    ->title('Consulta eliminada')
                    ->body('La consulta ha sido enviada a la papelera.')
                    ->send();
            });
    }
}
