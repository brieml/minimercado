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

    /**
     * Módulos a los que puede entrar cada rol (nombre del recurso en las rutas).
     *
     * Es la única fuente de verdad: las rutas (middleware `role`), el menú lateral
     * y el dashboard se construyen a partir de esta tabla.
     *
     * - Administrador: todos los módulos.
     * - Cajero: registra ventas (que generan la factura) y clientes, y consulta las facturas.
     * - Inventario: administra productos, categorías y proveedores.
     *
     * @var array<string, list<string>>
     */
    public const MODULOS_POR_ROL = [
        'Administrador' => ['productos', 'categorias', 'proveedores', 'clientes', 'facturas', 'ventas', 'usuarios'],
        'Cajero' => ['clientes', 'facturas', 'ventas'],
        'Inventario' => ['productos', 'categorias', 'proveedores'],
    ];

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
     * Indica si el rol del usuario le da acceso al módulo.
     */
    public function puedeAccederA(string $modulo): bool
    {
        return in_array($modulo, self::MODULOS_POR_ROL[$this->rol] ?? [], true);
    }

    /**
     * Roles que tienen acceso al módulo.
     *
     * @return list<string>
     */
    public static function rolesConAccesoA(string $modulo): array
    {
        return array_keys(array_filter(
            self::MODULOS_POR_ROL,
            fn (array $modulos) => in_array($modulo, $modulos, true),
        ));
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
