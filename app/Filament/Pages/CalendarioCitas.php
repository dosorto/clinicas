<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\CitasCalendar;
use App\Models\Citas;
use Illuminate\Support\Facades\Auth;

class CalendarioCitas extends Page
{
    /* ───────── Configuración de la Page ───────── */
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string  $view           = 'filament.pages.calendario-citas';

    /* ───────── Datos que pasaremos a la vista ─── */
    public array $eventos = [];   // ¡debe existir!

    /* ───────── Widgets en el header (opcional) ── */
    protected function getHeaderWidgets(): array
    {
        return [
            CitasCalendar::class, // quita esta línea si NO quieres widget arriba
        ];
    }

    /* ───────── Cargar eventos al montar la Page ─ */
 public function mount(): void
{
    $medico = Auth::user()->medico;

    $this->eventos = Citas::query()
        ->where('medico_id', $medico?->id)
        ->where('estado',   '!=', 'Cancelado')
        ->get()
        ->map(fn ($cita) => [
            'title' => $cita->motivo,
            'start' => "{$cita->fecha}T{$cita->hora}",
        ])
        ->values()
        ->toArray();

    // logger($this->eventos);
}



    /* ───────── Pasar variables a la Blade ─────── */
    protected function getViewData(): array
    {
        return [
            'eventos' => $this->eventos,
        ];
    }
}
