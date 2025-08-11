<?php

namespace App\Filament\Resources\FacturaDisenoResource\Pages;

use App\Filament\Resources\FacturaDisenoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacturaDisenos extends ListRecords
{
    protected static string $resource = FacturaDisenoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
