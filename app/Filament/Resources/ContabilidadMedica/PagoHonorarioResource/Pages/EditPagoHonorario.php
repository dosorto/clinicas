<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPagoHonorario extends EditRecord
{
    protected static string $resource = PagoHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
