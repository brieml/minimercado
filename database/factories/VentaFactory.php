<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'factura_id' => Factura::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->randomFloat(3, 1, 10),
        ];
    }
}
