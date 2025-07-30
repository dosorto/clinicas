<?php

namespace App\Filament\Widgets;

use App\Models\Citas;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CitasCalendar extends FullCalendarWidget
{
    /**
     * Devuelve los eventos que aparecerán en el calendario
     *
     * @param  array  $fetchInfo  Contiene 'start' y 'end' (ISO-8601) del rango visible
     * @return array              Lista de eventos formateados para FullCalendar
     */
    public function fetchEvents(array $fetchInfo): array
    {
        $medico = Auth::user()->medico;

        if (! $medico) {
            return [];
        }

        return Citas::query()
            ->where('medico_id', $medico->id)
            ->where('estado', '!=', 'Cancelado')
            ->whereDate('fecha', '>=', $fetchInfo['start'])
            ->whereDate('fecha', '<=', $fetchInfo['end'])
            ->get()
            ->map(fn ($cita) => [
                'id'     => $cita->id,
                'title'  => $cita->motivo,
                'start'  => $cita->fecha,        // día completo
                'allDay' => true,
                'color'  => match ($cita->estado) {
                    'Pendiente'   => '#f59e0b',
                    'Confirmado'  => '#3b82f6',
                    'En proceso'  => '#10b981',
                    default       => '#6b7280',
                },
            ])
            ->toArray();
    }

    /**
     * Opciones de configuración de FullCalendar
     *
     * @return array
     */
    protected function getFullCalendarOptions(): array
    {
        return [
            'locale'           => 'es',
            'initialView'      => 'dayGridMonth',
            'height'           => 'auto',
            'displayEventTime' => false,
            'headerToolbar'    => [
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
        ];
    }
}
