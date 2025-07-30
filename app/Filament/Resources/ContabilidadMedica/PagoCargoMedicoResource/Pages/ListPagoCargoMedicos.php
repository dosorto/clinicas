<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagoCargoMedicos extends ListRecords
{
    protected static string $resource = PagoCargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
