<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLiquidacionDetalle extends ViewRecord
{
    protected static string $resource = LiquidacionDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
