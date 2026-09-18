<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::create([
            'nombre' => 'Cliente General',
            'direccion' => null,
            'telefono' => null,
        ]);

        Cliente::create([
            'nombre' => 'Juan Pérez',
            'direccion' => 'Barrio Centro',
            'telefono' => '3001112233',
        ]);

        Cliente::create([
            'nombre' => 'María López',
            'direccion' => 'Barrio La Palma',
            'telefono' => '3002223344',
        ]);
    }
}
