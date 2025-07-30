<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPagoHonorario extends ViewRecord
{
    protected static string $resource = PagoHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
