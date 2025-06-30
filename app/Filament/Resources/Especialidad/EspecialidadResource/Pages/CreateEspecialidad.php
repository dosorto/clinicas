<?php

namespace App\Filament\Resources\Especialidad\EspecialidadResource\Pages;

use App\Filament\Resources\Especialidad\EspecialidadResource;
use Filament\Actions;
use Filament\Actions\Action; 
use Filament\Resources\Pages\CreateRecord;

class CreateEspecialidad extends CreateRecord
{
    protected static string $resource = EspecialidadResource::class;

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear Especialidad')
                ->submit('create')
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
        return 'Especialidad creada exitosamente';
    }
}