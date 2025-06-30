<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedico extends EditRecord
{
    protected static string $resource = MedicoResource::class;

    protected static ?string $title = 'Editar Médico'; // Título personalizado

    protected function getHeaderActions(): array
    {
        return []; // Elimina acciones del header
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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Médico actualizado correctamente';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}