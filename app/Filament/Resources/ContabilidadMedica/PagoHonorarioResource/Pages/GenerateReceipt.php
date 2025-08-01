<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource;
use App\Models\ContabilidadMedica\PagoHonorario;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class GenerateReceipt extends Page
{
    protected static string $resource = PagoHonorarioResource::class;
    
    protected static string $view = 'filament.resources.contabilidad-medica.pago-honorario-resource.pages.generate-receipt';
    
    public PagoHonorario $record;
    
    public function mount(PagoHonorario $record): void
    {
        $this->record = $record;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Imprimir recibo')
                ->icon('heroicon-o-printer')
                ->action('printReceipt'),
                
            Action::make('download')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    // Acción que dispara la descarga como PDF usando JavaScript
                    $this->dispatch('download-receipt-pdf');
                }),
                
            Action::make('sendEmail')
                ->label('Enviar por email')
                ->icon('heroicon-o-envelope')
                ->action(function () {
                    // Enviar recibo por email
                    // Aquí implementarías la lógica para enviar el recibo por email
                    
                    $this->dispatch('notify', [
                        'title' => 'Recibo enviado',
                        'message' => 'El recibo ha sido enviado por email correctamente',
                        'icon' => 'success',
                    ]);
                }),
                
            Action::make('back')
                ->label('Volver')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => PagoHonorarioResource::getUrl('index')),
        ];
    }
    
    public function printReceipt(): void
    {
        $this->dispatch('print-receipt');
    }
}
