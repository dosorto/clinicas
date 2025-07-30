<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiquidacionHonorarios extends ListRecords
{
    protected static string $resource = LiquidacionHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
