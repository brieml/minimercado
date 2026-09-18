<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        Proveedor::create([
            'nombre' => 'Distribuidora La Economía',
            'direccion' => 'Sincelejo',
            'telefono' => '3001234567',
        ]);

        Proveedor::create([
            'nombre' => 'Distribuciones Sucre',
            'direccion' => 'Sincelejo',
            'telefono' => '3012345678',
        ]);

        Proveedor::create([
            'nombre' => 'Comercializadora Caribe',
            'direccion' => 'Sincelejo',
            'telefono' => '3023456789',
        ]);
    }
}
