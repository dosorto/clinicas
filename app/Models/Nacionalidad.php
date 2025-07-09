<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nacionalidad extends Model
{
    /** @use HasFactory<\Database\Factories\NacionalidadFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'nacionalidades';
    protected $fillable = [
        'nacionalidad',
    ];

    public function personas(): HasMany{
        return $this->hasMany(Persona::class);
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }
}
