<?php

namespace App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource\Pages;

use App\Filament\Resources\EspecialidadMedico\EspecialidadMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEspecialidadMedico extends EditRecord
{
    protected static string $resource = EspecialidadMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
