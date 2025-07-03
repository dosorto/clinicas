<?php

namespace App\Filament\Resources\Receta\RecetaResource\Pages;

use App\Filament\Resources\Receta\RecetaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReceta extends CreateRecord
{
    protected static string $resource = RecetaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver al listado')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Receta creada exitosamente';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        return $data;
    }
}
