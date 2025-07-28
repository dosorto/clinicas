<?php

namespace App\Filament\Pages;

use App\Models\Citas;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CalendarioCitas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.calendario-citas';

    public $eventos = [];

    public function mount()
    {
        $medico = Auth::user()->medico; // Asumiendo que el usuario tiene relación 'medico'

        $this->eventos = Citas::where('medico_id', $medico->id)
            ->get()
            ->map(function ($cita) {
                return [
                    'title' => $cita->motivo,
                    'start' => $cita->fecha . 'T' . $cita->hora,
                    'id' => $cita->id,
                ];
            });
    }

    protected function getViewData(): array
    {
        return [
            'eventos' => $this->eventos,
        ];
    }
}
