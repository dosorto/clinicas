<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPagoHonorarios extends ListRecords
{
    protected static string $resource = PagoHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
