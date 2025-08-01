<?php

namespace App\Filament\Resources\NominaMedicaSimpleResource\Pages;

use App\Filament\Resources\NominaMedicaSimpleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNominaMedicaSimples extends ManageRecords
{
    protected static string $resource = NominaMedicaSimpleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Nueva Nómina')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTitle(): string
    {
        return 'Nómina Médica';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Puedes agregar widgets de resumen aquí
        ];
    }
}
