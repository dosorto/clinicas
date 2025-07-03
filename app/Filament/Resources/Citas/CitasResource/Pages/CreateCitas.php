<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCitas extends CreateRecord
{
    protected static string $resource = CitasResource::class;

    /**
     * Después de crear, redirige al listado de Citas.
     */
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}