<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiquidacionDetalles extends ListRecords
{
    protected static string $resource = LiquidacionDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
