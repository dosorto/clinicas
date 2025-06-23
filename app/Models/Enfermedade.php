<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enfermedade extends Model
{
    /** @use HasFactory<\Database\Factories\EnfermedadeFactory> */
    use HasFactory;
    use SoftDeletes;


    protected $table = 'enfermedades';  

    public function enfermedades_paciente(){
        return $this->hasMany(Enfermedades_Paciente::class, 'enfermedad_id');
    }
}
