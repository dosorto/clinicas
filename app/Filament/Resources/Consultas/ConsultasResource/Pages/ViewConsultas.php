<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Filament\Resources\Facturas\FacturasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Consultas\Widgets\FacturacionStatus;


class ViewConsultas extends ViewRecord
{
    protected static string $resource = ConsultasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // En ViewConsultas.php agregar botón:
            Actions\Action::make('agregar_servicios')
                ->label('Agregar Servicios')
                ->icon('heroicon-o-plus-circle')
                ->color('info')
                ->url(fn () => "/admin/consultas/consultas/{$this->record->id}/servicios")
                ->visible(fn () => !$this->record->facturas()->exists()),
                        
            // ---- BOTÓN CREAR FACTURA -------------------------------
            Actions\Action::make('crear_factura')
                ->label('Crear Factura')
                ->icon('heroicon-o-receipt-percent')
                ->color('success')
                ->url(function ($record) {
                    $subtotal = $this->getServiciosSubtotal($record);
                    $impuestoTotal = $this->getServiciosImpuesto($record);
                    return FacturasResource::getUrl('create', [
                        'consulta_id' => $record->id,
                        'subtotal' => $subtotal,
                        'impuesto_total' => $impuestoTotal
                    ]);
                })
                ->visible(fn ($record) => ! $record->facturas()->exists()),

            // ---- BOTÓN VER FACTURA ---------------------------------
            Actions\Action::make('ver_factura')
                ->label('Ver Factura')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) =>
                    FacturasResource::getUrl('view', [
                        'record' => $record->facturas()->first()?->id,
                    ])
                )
                ->visible(fn ($record) => $record->facturas()->exists()),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí podrías agregar widgets si necesitas mostrar información adicional
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widget personalizado para mostrar el estado de facturación
            \App\Filament\Resources\Consultas\Widgets\FacturacionStatus::class,
            
        ];
    }

    private function getServiciosSubtotal($record): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $record->id)
            ->whereNull('factura_id')
            ->sum('subtotal');
    }

    private function getServiciosImpuesto($record): float
    {
        return \App\Models\FacturaDetalle::where('consulta_id', $record->id)
            ->whereNull('factura_id')
            ->sum('impuesto_monto');
    }

}
