<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionDetalleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiquidacionDetalle extends EditRecord
{
    protected static string $resource = LiquidacionDetalleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
