<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Generar liquidaciones automáticamente según la configuración
        $frecuencia = config('contabilidad.liquidacion_programada', 'diaria');
        
        switch ($frecuencia) {
            case 'diaria':
                $schedule->command('contabilidad:generar-liquidaciones')
                    ->dailyAt('23:00')
                    ->appendOutputTo(storage_path('logs/liquidaciones-automaticas.log'));
                break;
            case 'semanal':
                $schedule->command('contabilidad:generar-liquidaciones')
                    ->weeklyOn(5, '23:00') // Viernes a las 23:00
                    ->appendOutputTo(storage_path('logs/liquidaciones-automaticas.log'));
                break;
            case 'mensual':
                $schedule->command('contabilidad:generar-liquidaciones')
                    ->monthlyOn(config('contabilidad.dia_liquidacion_mensual', 28), '23:00')
                    ->appendOutputTo(storage_path('logs/liquidaciones-automaticas.log'));
                break;
        }
        
        // Generar pagos automáticos si está habilitado
        if (config('contabilidad.pagos_automaticos', false)) {
            $diaPago = config('contabilidad.dia_pago_automatico', 15);
            $schedule->command('contabilidad:generar-pagos')
                ->monthlyOn($diaPago, '09:00')
                ->appendOutputTo(storage_path('logs/pagos-automaticos.log'));
        }
        
        // Generar nómina automática si está habilitado
        if (config('contabilidad.nomina_automatica', false)) {
            $diaNomina = config('contabilidad.dia_nomina_automatica', 30);
            $schedule->command('nomina:generar --guardar')
                ->monthlyOn($diaNomina, '10:00')
                ->appendOutputTo(storage_path('logs/nomina-automatica.log'));
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
