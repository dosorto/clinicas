<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidad extends Model
{
    use HasFactory;

    protected $fillable = [
        'especialidad',
    ];

    public function medicos()
    {
    return $this->belongsToMany(Medico::class, 'especialidad_medicos', 'especialidad_id', 'medico_id');
    }
}
