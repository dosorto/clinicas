<?php

namespace App\Notifications;

use App\Models\ContabilidadMedica\PagoHonorario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class PagoHonorarioRealizado extends Notification implements ShouldQueue
{
    use Queueable;

    protected $pago;

    /**
     * Create a new notification instance.
     */
    public function __construct(PagoHonorario $pago)
    {
        $this->pago = $pago;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $medico = $this->pago->liquidacion->medico->persona->nombre_completo ?? 'Estimado médico';
        $reciboUrl = route('filament.admin.resources.contabilidad-medica.pago-honorarios.generar-recibo', ['pagoId' => $this->pago->id]);
        
        return (new MailMessage)
            ->subject('Pago de Honorarios Realizado')
            ->greeting('Hola Dr. ' . $medico)
            ->line('Se ha realizado un pago de honorarios a su favor.')
            ->line('Pago #' . $this->pago->id)
            ->line('Monto: L. ' . number_format($this->pago->monto, 2))
            ->line('Monto neto (después de retenciones): L. ' . number_format($this->pago->monto_neto, 2))
            ->line('Fecha: ' . optional($this->pago->fecha_pago)->format('d/m/Y'))
            ->line('Método: ' . ucfirst($this->pago->metodo_pago))
            ->line('Referencia: ' . $this->pago->referencia_pago)
            ->action('Descargar Recibo', $reciboUrl)
            ->line('Gracias por su preferencia.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pago_id' => $this->pago->id,
            'liquidacion_id' => $this->pago->liquidacion_id,
            'monto' => $this->pago->monto,
            'monto_neto' => $this->pago->monto_neto,
            'fecha_pago' => optional($this->pago->fecha_pago)->format('d/m/Y'),
            'metodo_pago' => $this->pago->metodo_pago,
            'referencia_pago' => $this->pago->referencia_pago,
        ];
    }
    
    /**
     * Enviar una notificación de Filament.
     */
    public static function sendFilamentNotification(PagoHonorario $pago): void
    {
        FilamentNotification::make()
            ->title('Pago de Honorarios Realizado')
            ->body('Se ha realizado el pago #' . $pago->id . ' por L. ' . number_format($pago->monto, 2))
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Ver Pago')
                    ->url(route('filament.admin.resources.contabilidad-medica.pago-honorarios.view', ['record' => $pago->id])),
                \Filament\Notifications\Actions\Action::make('download')
                    ->label('Descargar Recibo')
                    ->url(route('filament.admin.resources.contabilidad-medica.pago-honorarios.generar-recibo', ['pagoId' => $pago->id]))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase($pago->liquidacion->medico->user);
    }
}
