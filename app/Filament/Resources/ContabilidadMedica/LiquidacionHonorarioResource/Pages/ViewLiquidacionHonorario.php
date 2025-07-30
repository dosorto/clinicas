<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLiquidacionHonorario extends ViewRecord
{
    protected static string $resource = LiquidacionHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
