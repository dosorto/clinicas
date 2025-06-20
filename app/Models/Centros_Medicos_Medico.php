<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Centros_Medicos_Medico extends Model
{
    /** @use HasFactory<\Database\Factories\CentrosMedicosMedicoFactory> */
    use HasFactory;

    protected $table = 'centros_medicos_medicos';

    public function centro_medico(){
        return $this->belongsTo(Centros_Medico::class, 'centro_medico_id');
    }

    public function medico(){
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}
