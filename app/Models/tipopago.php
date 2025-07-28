<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\TenantScoped;   


class TipoPago extends ModeloBase        
{
    use HasFactory, SoftDeletes;       
    use TenantScoped;

    protected string $tenantKeyName = 'centro_id';   

    protected $fillable = [
        'nombre',
        'descripcion',
        'centro_id',    
        'created_by',   
        'updated_by',    
        'deleted_by',   
    ];

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

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
