<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        Categoria::create([
            'descripcion' => 'Bebidas',
        ]);

        Categoria::create([
            'descripcion' => 'Alimentos',
        ]);

        Categoria::create([
            'descripcion' => 'Snacks',
        ]);

        Categoria::create([
            'descripcion' => 'Aseo',
        ]);

        Categoria::create([
            'descripcion' => 'Cuidado personal',
        ]);
    }
}
