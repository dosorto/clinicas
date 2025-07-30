<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiquidacionHonorario extends EditRecord
{
    protected static string $resource = LiquidacionHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
