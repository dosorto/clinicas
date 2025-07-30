<?php

namespace App\Services;

use App\Models\CAIAutorizaciones;
use App\Models\CAI_Correlativos;
use Illuminate\Support\Facades\DB;

class CaiNumerador
{
    /** Genera y persiste el próximo correlativo, o lanza excepción. */
    public static function generar(int $caiId, int $usuarioId, int $centroId): CAI_Correlativos
    {
        return DB::transaction(function () use ($caiId, $usuarioId, $centroId) {
            $cai = CAIAutorizaciones::lockForUpdate()->findOrFail($caiId);

            if (! $cai->esValida()) {
                throw new \RuntimeException('CAI no válido.');
            }

            $numero = $cai->numero_actual ?? $cai->rango_inicial;   
            $numeroFormateado  = self::formatear($numero, $centroId);

            $correlativo = CAI_Correlativos::create([
                'autorizacion_id' => $cai->id,
                'numero_factura'  => $numeroFormateado,
                'fecha_emision'   => now(),
                'usuario_id'      => $usuarioId,
                'centro_id'       => $centroId,
            ]);

            $cai->incrementarNumero();          // avanza +1 y refresca estado
            return $correlativo;
        });
    }

    /** Formato personalizable */
    protected static function formatear(int $numero, int $centroId): string
    {
        $plantilla = config('cai.formato', '{sucursal}-{caja}-{tipo}-{seq}');

        return strtr($plantilla, [
            '{sucursal}' => str_pad($centroId, 3, '0', STR_PAD_LEFT),
            '{caja}'     => str_pad(auth()->user()->caja ?? 1, 3, '0', STR_PAD_LEFT),
            '{tipo}'     => '01',                         // contado
            '{seq}'      => str_pad($numero, 8, '0', STR_PAD_LEFT),
        ]);
    }
}
