<?php

namespace App\Filament\Resources\Citas\CitasResource\Pages;

use App\Filament\Resources\Citas\CitasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCitas extends ListRecords
{
    protected static string $resource = CitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
