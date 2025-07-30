<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagoCargoMedico extends EditRecord
{
    protected static string $resource = PagoCargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
