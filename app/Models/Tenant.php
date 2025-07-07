<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $table = 'tenants';
    protected $fillable = [
        'centro_id',
        'name',
        'domain',
        'database',
    ];

    public function centro()
    {
        return $this->belongsTo(\App\Models\Centros_Medico::class, 'centro_id');
    }
}
