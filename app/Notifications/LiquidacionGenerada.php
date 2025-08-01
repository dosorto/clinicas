<?php

namespace App\Notifications;

use App\Models\ContabilidadMedica\LiquidacionHonorario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class LiquidacionGenerada extends Notification implements ShouldQueue
{
    use Queueable;

    protected $liquidacion;

    /**
     * Create a new notification instance.
     */
    public function __construct(LiquidacionHonorario $liquidacion)
    {
        $this->liquidacion = $liquidacion;
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
        $url = route('filament.admin.resources.contabilidad-medica.liquidacion-honorarios.view', ['record' => $this->liquidacion->id]);
        
        return (new MailMessage)
            ->subject('Nueva Liquidación de Honorarios')
            ->greeting('Hola Dr. ' . ($this->liquidacion->medico->persona->nombre_completo ?? ''))
            ->line('Se ha generado una nueva liquidación de honorarios para usted.')
            ->line('Liquidación #' . $this->liquidacion->id)
            ->line('Monto: L. ' . number_format($this->liquidacion->monto_total, 2))
            ->line('Período: ' . optional($this->liquidacion->periodo_inicio)->format('d/m/Y') . ' - ' . optional($this->liquidacion->periodo_fin)->format('d/m/Y'))
            ->action('Ver Liquidación', $url)
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
            'liquidacion_id' => $this->liquidacion->id,
            'monto' => $this->liquidacion->monto_total,
            'medico' => $this->liquidacion->medico->persona->nombre_completo ?? 'Médico',
            'periodo_inicio' => optional($this->liquidacion->periodo_inicio)->format('d/m/Y'),
            'periodo_fin' => optional($this->liquidacion->periodo_fin)->format('d/m/Y'),
        ];
    }
    
    /**
     * Enviar una notificación de Filament.
     */
    public static function sendFilamentNotification(LiquidacionHonorario $liquidacion): void
    {
        FilamentNotification::make()
            ->title('Nueva Liquidación de Honorarios')
            ->body('Se ha generado la liquidación #' . $liquidacion->id . ' por L. ' . number_format($liquidacion->monto_total, 2))
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Ver Liquidación')
                    ->url(route('filament.admin.resources.contabilidad-medica.liquidacion-honorarios.view', ['record' => $liquidacion->id])),
            ])
            ->sendToDatabase($liquidacion->medico->user);
    }
}
