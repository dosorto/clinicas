<?php

namespace App\Filament\Resources\FacturaDisenoResource\Pages;

use App\Filament\Resources\FacturaDisenoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFacturaDiseno extends CreateRecord
{
    protected static string $resource = FacturaDisenoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar el centro médico actual
        $data['centro_id'] = session('current_centro_id');
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
