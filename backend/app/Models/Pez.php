<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pez extends Model
{
    use HasFactory;

    protected $table = 'peces';

    protected $fillable = [
        'codigo',
        'nombre',
        'especie',
        'tipo_agua',
        'longitud_cm',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'longitud_cm' => 'float',
        ];
    }
}
