<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPagoCargoMedico extends ViewRecord
{
    protected static string $resource = PagoCargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
