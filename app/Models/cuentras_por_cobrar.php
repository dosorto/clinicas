<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuentas_Por_Cobrar extends Model
{
    /** @use HasFactory<\Database\Factories\Cuentas_Por_CobrarFactory> */
    use HasFactory;
    
    protected $fillable = [
        'factura_id',
        'paciente_id',
        'pagos_factura_id',
        'saldo_pendiente',
        'fecha_vencimiento',
        'estado_cuentas_por_cobrar',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'fecha_vencimiento',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->centro_id)) {
                $user = auth()->user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }
        });
    }
}
