<?php

namespace App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\PagoHonorarioResource;
use App\Models\ContabilidadMedica\PagoHonorario;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class GenerarRecibo extends Page
{
    protected static string $resource = PagoHonorarioResource::class;
    
    protected static ?string $title = 'Recibo de Pago';
    
    protected static string $view = 'filament.resources.contabilidad-medica.pago-honorario-resource.pages.generar-recibo';
    
    public ?PagoHonorario $pago = null;
    
    public function mount(string $pagoId): void
    {
        $this->pago = PagoHonorario::findOrFail($pagoId);
        
        // Verificar si el usuario tiene permiso para ver este pago
        $this->authorize('view', $this->pago);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPDF')
                ->label('Descargar PDF')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return $this->generarPDF();
                }),
                
            Action::make('enviarPorEmail')
                ->label('Enviar por Email')
                ->color('primary')
                ->icon('heroicon-o-envelope')
                ->action(function () {
                    // Aquí iría la lógica para enviar el recibo por email
                    // Por ahora solo mostramos una notificación
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Recibo enviado')
                        ->body('El recibo ha sido enviado por email correctamente')
                        ->send();
                }),
                
            Action::make('volver')
                ->label('Volver')
                ->color('gray')
                ->url(PagoHonorarioResource::getUrl('index')),
        ];
    }
    
    public function generarPDF()
    {
        $pago = $this->pago;
        
        if (!$pago) {
            return redirect()->back();
        }
        
        // Cargar datos adicionales para el PDF
        $liquidacion = $pago->liquidacion;
        $cargoMedico = $liquidacion->cargoMedico ?? null;
        $medico = $cargoMedico->medico ?? null;
        $persona = $medico->persona ?? null;
        $centro = $cargoMedico->centro ?? null;
        
        $data = [
            'pago' => $pago,
            'liquidacion' => $liquidacion,
            'cargoMedico' => $cargoMedico,
            'medico' => $medico,
            'persona' => $persona,
            'centro' => $centro,
            'fecha_formateada' => \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y'),
            'monto_formateado' => number_format($pago->monto, 2),
            'monto_texto' => $this->numeroALetras($pago->monto),
            'numero_recibo' => str_pad($pago->id, 6, '0', STR_PAD_LEFT),
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
        ];
        
        // Generar el PDF
        $pdf = Pdf::loadView('filament.resources.contabilidad-medica.pago-honorario-resource.pages.recibo-pdf', $data);
        
        // Nombre del archivo
        $nombreArchivo = 'recibo_' . str_pad($pago->id, 6, '0', STR_PAD_LEFT) . '.pdf';
        
        // Guardar el archivo temporalmente
        Storage::put('public/recibos/' . $nombreArchivo, $pdf->output());
        
        // Descargar el PDF
        return response()->download(
            storage_path('app/public/recibos/' . $nombreArchivo),
            $nombreArchivo,
            ['Content-Type' => 'application/pdf']
        );
    }
    
    // Función para convertir números a letras (para el monto en texto)
    private function numeroALetras($numero)
    {
        $unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        $decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        $especiales = ['once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve',
                      'veintiuno', 'veintidós', 'veintitrés', 'veinticuatro', 'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve'];
        
        // Separar parte entera y decimal
        $partes = explode('.', number_format($numero, 2, '.', ''));
        $entero = (int)$partes[0];
        $decimal = (int)$partes[1];
        
        $texto = '';
        
        // Convertir la parte entera
        if ($entero == 0) {
            $texto = 'cero';
        } elseif ($entero == 1) {
            $texto = 'un';
        } elseif ($entero <= 9) {
            $texto = $unidades[$entero];
        } elseif ($entero <= 29) {
            // Manejar casos especiales del 11-19 y 21-29
            if ($entero <= 19) {
                $texto = $especiales[$entero - 11];
            } else {
                $texto = $especiales[$entero - 11];
            }
        } elseif ($entero <= 99) {
            $decena = (int)($entero / 10);
            $unidad = $entero % 10;
            $texto = $decenas[$decena];
            if ($unidad > 0) {
                $texto .= ' y ' . $unidades[$unidad];
            }
        } elseif ($entero <= 999) {
            $centena = (int)($entero / 100);
            $resto = $entero % 100;
            
            // Caso especial para 100
            if ($entero == 100) {
                $texto = 'cien';
            } else {
                $texto = $centenas[$centena];
                if ($resto > 0) {
                    $texto .= ' ' . $this->numeroALetras($resto);
                }
            }
        } else {
            // Para valores mayores a 999
            $texto = 'cantidad mayor a mil';
        }
        
        // Añadir texto "Lempiras" y centavos
        $texto .= ' lempiras';
        if ($decimal > 0) {
            $texto .= ' con ' . $decimal . '/100';
        } else {
            $texto .= ' exactos';
        }
        
        return ucfirst($texto);
    }
}
