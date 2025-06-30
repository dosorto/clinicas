<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedico extends EditRecord
{
    protected static string $resource = MedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
