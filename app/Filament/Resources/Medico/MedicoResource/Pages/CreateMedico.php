<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use App\Models\Medico;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMedico extends CreateRecord
{
    protected static string $resource = MedicoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return MedicoResource::handleMedicoCreation($data);
    }
}
