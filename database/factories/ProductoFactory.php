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
            'precio' => fake()->randomFloat(2, 500, 50000),
            'tiene_gramaje' => false,
            'categoria_id' => Categoria::factory(),
            'proveedor_id' => Proveedor::factory(),
        ];
    }

    /**
     * Producto que se vende por peso: admite decimales en la cantidad.
     */
    public function conGramaje(): static
    {
        return $this->state(fn (array $attributes) => ['tiene_gramaje' => true]);
    }
}
