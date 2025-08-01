<?php

namespace App\Filament\Resources\NominaSimpleResource\Pages;

use App\Filament\Resources\NominaSimpleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNominaSimples extends ManageRecords
{
    protected static string $resource = NominaSimpleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ayuda')
                ->label('¿Cómo usar?')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->modalHeading('Cómo generar nóminas')
                ->modalDescription('Este sistema te permite generar nóminas de manera sencilla:')
                ->modalContent(view('filament.pages.ayuda-nomina')),
        ];
    }
}
