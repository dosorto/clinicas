<?php

namespace App\Services;

use App\Models\CAIAutorizaciones;
use App\Models\CAI_Correlativos;
use App\Models\Factura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CaiNumerador
{
    /**
     * Genera un nuevo correlativo CAI para una factura
     */
    public static function generar(int $caiId, int $usuarioId, int $centroId, ?int $facturaId = null): CAI_Correlativos
    {
        return DB::transaction(function () use ($caiId, $usuarioId, $centroId, $facturaId) {
            // Obtener CAI con bloqueo para evitar concurrencia
            $cai = CAIAutorizaciones::where('id', $caiId)
                ->where('estado', 'ACTIVA')
                ->lockForUpdate()
                ->first();

            if (!$cai) {
                throw new \Exception('CAI no disponible o inactivo');
            }

            // Verificar que no esté vencido
            if ($cai->fecha_limite < now()->toDateString()) {
                $cai->update(['estado' => 'VENCIDA']);
                throw new \Exception('CAI vencido desde el ' . $cai->fecha_limite->format('d/m/Y'));
            }

            // Inicializar numero_actual si es null
            if (is_null($cai->numero_actual)) {
                $cai->numero_actual = $cai->rango_inicial;
                $cai->save();
            }

            // Verificar que no esté agotado DESPUÉS de inicializar
            if ($cai->numero_actual > $cai->rango_final) {
                $cai->update(['estado' => 'AGOTADA']);
                throw new \Exception('CAI agotado - no quedan números disponibles');
            }

            // Obtener el siguiente número
            $numeroCorrelativo = $cai->numero_actual;
            
            // Validación adicional de seguridad
            if (is_null($numeroCorrelativo)) {
                throw new \Exception('Error interno: número correlativo no inicializado');
            }
            
            // Generar el número de factura formateado
            $numeroFactura = self::formatearNumeroFactura($cai->rtn, $numeroCorrelativo);

            // Crear el correlativo
            $correlativo = CAI_Correlativos::create([
                'autorizacion_id' => $cai->id,
                'numero_correlativo' => $numeroCorrelativo,
                'numero_factura' => $numeroFactura,
                'fecha_emision' => now(),
                'factura_id' => $facturaId,
                'usuario_id' => $usuarioId,
                'centro_id' => $centroId,
                'created_by' => $usuarioId,
            ]);

            // Incrementar el número actual del CAI
            $cai->increment('numero_actual');

            // Verificar si se agotó después del incremento
            if ($cai->numero_actual > $cai->rango_final) {
                $cai->update(['estado' => 'AGOTADA']);
                Log::info("CAI agotado: {$cai->cai_codigo}");
            }

            return $correlativo;
        });
    }

    /**
     * Genera correlativo automáticamente para una factura
     */
    public static function generarParaFactura(Factura $factura): ?CAI_Correlativos
    {
        $cai = self::obtenerCAIDisponible($factura->centro_id);
        
        if (!$cai) {
            Log::warning("No hay CAI disponible para centro {$factura->centro_id}");
            return null;
        }

        try {
            return self::generar(
                $cai->id, 
                $factura->created_by ?? Auth::id() ?? 1,
                $factura->centro_id,
                $factura->id
            );
        } catch (\Exception $e) {
            Log::error("Error generando CAI para factura {$factura->id}: " . $e->getMessage());
            return null;
        }
    }

    private static function formatearNumeroFactura(string $rtn, int $numeroCorrelativo): string
    {
        // Formato: 001-001-01-00000001
        // Formato estándar para Honduras SAR
        $establecimiento = '001';  // Código de establecimiento 
        $puntoEmision = '001';     // Punto de emisión
        $tipoDocumento = '01';     // Tipo de documento (01 = Factura)
        
        // La cuarta parte es el correlativo que incrementa (8 dígitos)
        $correlativo = str_pad($numeroCorrelativo, 8, '0', STR_PAD_LEFT);
        
        return "{$establecimiento}-{$puntoEmision}-{$tipoDocumento}-{$correlativo}";
    }

    public static function obtenerCAIDisponible(int $centroId): ?CAIAutorizaciones
    {
        return CAIAutorizaciones::where('centro_id', $centroId)
            ->where('estado', 'ACTIVA')
            ->where('fecha_limite', '>=', now()->toDateString())
            ->where(function($query) {
                $query->whereNull('numero_actual')
                      ->orWhere('numero_actual', '<=', DB::raw('rango_final'));
            })
            ->orderBy('fecha_limite', 'asc') // Usar el que vence primero
            ->first();
    }
}