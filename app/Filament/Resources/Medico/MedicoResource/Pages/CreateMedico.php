<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedico extends CreateRecord
{
    protected static string $resource = MedicoResource::class;

    protected static ?string $title = 'Crear Médico'; // Título personalizado en la página

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear Médico')
                ->submit('create')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->action(function () {
                    $this->create();
                    $this->redirect($this->getRedirectUrl());
                }),
                
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