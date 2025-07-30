<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CAI_Correlativos extends Model
{
    /** @use HasFactory<\Database\Factories\CAI_CorrelativosFactory> */
    use HasFactory;

    protected $table = 'cai_correlativos';

    protected $fillable = [
        'autorizacion_id',
        'numero_factura',
        'fecha_emision',
        'usuario_id',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'fecha_emision',
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
