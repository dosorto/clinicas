<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Enfermedades_Paciente;


class Enfermedade extends Model
{
    /** @use HasFactory<\Database\Factories\EnfermedadeFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'enfermedades',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $table = 'enfermedades';

    // Relaciones para mostrar quién creó/editó
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function enfermedades_paciente()
    {
        return $this->hasMany(Enfermedades_Paciente::class, 'enfermedad_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::deleting(function ($model) {
            $model->deleted_by = Auth::id();
            $model->save();
        });
    }
}