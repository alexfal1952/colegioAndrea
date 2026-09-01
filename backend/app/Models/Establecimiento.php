<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establecimiento extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function periodosEscolares(): HasMany
    {
        return $this->hasMany(PeriodoEscolar::class);
    }

    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class);
    }

    public static function default(): self
    {
        $codigo = config('school.default_establecimiento_codigo', 'DEFAULT');

        return static::query()->where('codigo', $codigo)->firstOrFail();
    }
}
