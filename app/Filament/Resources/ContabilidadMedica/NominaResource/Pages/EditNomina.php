<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNomina extends EditRecord
{
    protected static string $resource = NominaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->cerrada),
        ];
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();
        
        if ($this->record->cerrada) {
            $this->redirect(route('filament.admin.resources.contabilidad-medica.nominas.view', $this->record));
            
            \Filament\Notifications\Notification::make()
                ->title('Nómina cerrada')
                ->body('Esta nómina está cerrada y no puede ser editada.')
                ->warning()
                ->send();
        }
    }
}
