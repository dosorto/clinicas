<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Especialidad_Medico extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\EspecialidadMedicoFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'especialidad_medicos';
    protected $fillable = [
        'medico_id',
        'especialidad_id',
        'centro_id',
    ];
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    

    public static function newFactory()
    {
    return \Database\Factories\EspecialidadMedicoFactory::new();
    }
}
