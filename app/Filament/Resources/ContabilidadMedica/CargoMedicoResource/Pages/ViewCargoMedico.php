<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCargoMedico extends ViewRecord
{
    protected static string $resource = CargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
