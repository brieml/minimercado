<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descripcion' => fake()->words(3, true),
            'precio' => fake()->randomFloat(2, 1000, 50000),
            'categoria_id' => Categoria::factory(),
            'proveedor_id' => Proveedor::factory(),
        ];
    }
}
