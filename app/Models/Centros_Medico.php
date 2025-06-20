<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Centros_Medico extends Model
{
    /** @use HasFactory<\Database\Factories\CentrosMedicoFactory> */
    use HasFactory;

    protected $table = 'centros__medicos';

    protected $fillable = [
        'nombre_centro',
        'direccion',
        'telefono',
        'fotografia',
    ];

    public function centro_medico_medico() {
        return $this->hasMany(Centros_Medicos_Medico::class, 'centro_medico_id');
    }


}
