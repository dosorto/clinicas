<?php

namespace App\Filament\Resources\CentrosMedicosMedico\CentrosMedicosMedicoResource\Pages;

use App\Filament\Resources\CentrosMedicosMedico\CentrosMedicosMedicoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCentrosMedicosMedico extends EditRecord
{
    protected static string $resource = CentrosMedicosMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
