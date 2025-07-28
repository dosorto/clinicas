<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCargoMedico extends EditRecord
{
    protected static string $resource = CargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
