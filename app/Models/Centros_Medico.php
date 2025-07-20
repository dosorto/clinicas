<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Centros_Medico extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\CentrosMedicoFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'centros_medicos';

    protected $fillable = [
        'nombre_centro',
        'direccion',
        'telefono',
        'rtn',
        'fotografia',
    ];

    public function centro_medico_medico() {
        return $this->hasMany(Centros_Medicos_Medico::class, 'centro_medico_id');
    }
    
    public function medicos()
    {
        return $this->hasMany(
            \App\Models\Centros_Medicos_Medico::class,
            'centro_medico_id'
        );
    }

   
        


}
