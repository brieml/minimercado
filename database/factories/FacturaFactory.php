<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Factura>
 */
class FacturaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fecha' => fake()->dateTimeBetween('-1 month', 'now'),
            'cliente_id' => Cliente::factory(),
            'usuario_id' => Usuario::factory(),
        ];
    }
}
