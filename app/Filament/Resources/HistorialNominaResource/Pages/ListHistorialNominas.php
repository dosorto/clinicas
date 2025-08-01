<?php

namespace App\Filament\Resources\HistorialNominaResource\Pages;

use App\Filament\Resources\HistorialNominaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHistorialNominas extends ListRecords
{
    protected static string $resource = HistorialNominaResource::class;

    public function getTitle(): string
    {
        return 'Historial de Nóminas Médicas';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Las acciones se definen en el resource
        ];
    }
}
