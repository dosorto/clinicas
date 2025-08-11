<?php

namespace App\Filament\Resources\FacturaDisenoResource\Pages;

use App\Filament\Resources\FacturaDisenoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacturaDiseno extends EditRecord
{
    protected static string $resource = FacturaDisenoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
