<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nacionalidad extends Model
{
    /** @use HasFactory<\Database\Factories\NacionalidadFactory> */
    use HasFactory;

    protected $fillable = [
        'nacionalidad',
    ];

    public function personas(): HasMany{
        return $this->hasMany(Persona::class);
    }
}
