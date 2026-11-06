<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'correo' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'rol' => fake()->randomElement(Usuario::ROLES),
            'estado' => 'Activo',
        ];
    }

    /**
     * Usuario con rol Administrador.
     */
    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => ['rol' => 'Administrador']);
    }

    /**
     * Usuario con rol Cajero.
     */
    public function cajero(): static
    {
        return $this->state(fn (array $attributes) => ['rol' => 'Cajero']);
    }

    /**
     * Usuario con rol Inventario.
     */
    public function inventario(): static
    {
        return $this->state(fn (array $attributes) => ['rol' => 'Inventario']);
    }

    /**
     * Usuario con estado Inactivo.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => 'Inactivo']);
    }
}
