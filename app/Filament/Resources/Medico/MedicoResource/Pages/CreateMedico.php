<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedico extends CreateRecord
{
    protected static string $resource = MedicoResource::class;

    protected static ?string $title = 'Crear Médico';

    protected function getFormActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Médico')
                ->submit('create')
                ->icon('heroicon-o-user-plus'),
                
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

    protected function getHeaderActions(): array
    {
        return []; // Elimina acciones del header
    }
}