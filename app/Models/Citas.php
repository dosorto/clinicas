<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Citas extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\CitasFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'citas';
    
     protected $fillable = [
        'medico_id',
        'paciente_id',
        'fecha',
        'hora',
        'motivo',
        'estado',
        'centro_id',
    ];

    public function paciente(){
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function medico(){
        return $this->belongsTo(Medico::class, 'medico_id');
    }

        public function confirmar(): void
    {
        $this->update(['estado' => 'Confirmado']);
    }

    public function cancelar(): void
    {
        $this->update(['estado' => 'Cancelado']);
    }

   
}
