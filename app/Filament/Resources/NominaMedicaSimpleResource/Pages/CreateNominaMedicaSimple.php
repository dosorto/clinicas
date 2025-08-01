<?php

namespace App\Filament\Resources\NominaMedicaSimpleResource\Pages;

use App\Filament\Resources\NominaMedicaSimpleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class CreateNominaMedicaSimple extends CreateRecord
{
    protected static string $resource = NominaMedicaSimpleResource::class;

    public function getTitle(): string
    {
        return 'Crear Nueva Nómina Médica';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancelar')
                ->label('Cancelar')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Generar Nómina PDF')
            ->icon('heroicon-o-document-arrow-down');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $data = $this->data;
        
        // Construir URL para generar PDF
        $url = route('nomina.generar.pdf', [
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'incluir_pagados' => true,
            'incluir_pendientes' => false,
        ]);

        // Mostrar notificación
        Notification::make()
            ->title('Nómina Generada Exitosamente')
            ->body('La nómina médica ha sido procesada y está lista para descargar.')
            ->success()
            ->persistent()
            ->actions([
                \Filament\Notifications\Actions\Action::make('descargar')
                    ->label('Descargar PDF')
                    ->url($url)
                    ->openUrlInNewTab()
                    ->button(),
            ])
            ->send();
            
        // Redirigir con descarga automática
        $this->redirect($url);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // No crear ningún registro en la base de datos, solo procesar los datos
        // y generar el PDF. Retornamos un modelo temporal para que no falle.
        return new class extends \Illuminate\Database\Eloquent\Model {
            protected $table = 'temp_nomina';
            protected $fillable = ['*'];
            public function exists() { return true; }
            public function getKey() { return 1; }
            public function save(array $options = []) { return true; }
        };
    }
}
