<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'password',
        'rol',
        'estado',
    ];

    protected $hidden = [
        'password',

    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->nombre} {$this->apellido}"),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->correo,
        );
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->nombre, 0, 1).mb_substr($this->apellido, 0, 1));
    }
}
