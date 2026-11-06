<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Roles disponibles (enum `rol` de la migración).
     *
     * @var list<string>
     */
    public const ROLES = ['Administrador', 'Cajero', 'Inventario'];

    /**
     * Estados disponibles (enum `estado` de la migración).
     *
     * @var list<string>
     */
    public const ESTADOS = ['Activo', 'Inactivo'];

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

    /**
     * Nombre y apellido juntos.
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->nombre} {$this->apellido}"),
        );
    }

    /**
     * Iniciales para el avatar del usuario.
     */
    protected function iniciales(): Attribute
    {
        return Attribute::make(
            get: fn () => mb_strtoupper(mb_substr((string) $this->nombre, 0, 1).mb_substr((string) $this->apellido, 0, 1)),
        );
    }
}
