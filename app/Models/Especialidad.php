<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
// use App\Models\Traits\TenantScoped; 

class Especialidad extends ModeloBase
{
    use HasFactory;

    protected $fillable = [
        'especialidad'
    ];

    
    public function medicos()
    {
    return $this->belongsToMany(Medico::class, 'especialidad_medicos', 'especialidad_id', 'medico_id');
    }
}
