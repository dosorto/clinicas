<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCargoMedicos extends ListRecords
{
    protected static string $resource = CargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
