<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Centros_Medicos_Medico extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\CentrosMedicosMedicoFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'centros_medicos_medicos';

    protected $fillable = [
        'medico_id',
        'centro_medico_id',
        'horario_entrada',
        'horario_salida',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public function centro_medico(){
        return $this->belongsTo(Centros_Medico::class, 'centro_medico_id');
    }

    public function medico(){
        return $this->belongsTo(Medico::class, 'medico_id');
    }

   
}
