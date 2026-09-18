<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $bebidas = Categoria::where('descripcion', 'Bebidas')->first();
        $alimentos = Categoria::where('descripcion', 'Alimentos')->first();
        $snacks = Categoria::where('descripcion', 'Snacks')->first();
        $aseo = Categoria::where('descripcion', 'Aseo')->first();
        $cuidado = Categoria::where('descripcion', 'Cuidado personal')->first();

        $proveedor1 = Proveedor::first();
        $proveedor2 = Proveedor::skip(1)->first();
        $proveedor3 = Proveedor::skip(2)->first();

        Producto::create([
            'descripcion' => 'Gaseosa 1.5 Litros',
            'precio' => 6500,
            'categoria_id' => $bebidas->id,
            'proveedor_id' => $proveedor1->id,
        ]);

        Producto::create([
            'descripcion' => 'Agua 600 ml',
            'precio' => 2500,
            'categoria_id' => $bebidas->id,
            'proveedor_id' => $proveedor1->id,
        ]);

        Producto::create([
            'descripcion' => 'Arroz 1 Kg',
            'precio' => 4500,
            'categoria_id' => $alimentos->id,
            'proveedor_id' => $proveedor2->id,
        ]);

        Producto::create([
            'descripcion' => 'Pan tajado',
            'precio' => 5500,
            'categoria_id' => $alimentos->id,
            'proveedor_id' => $proveedor2->id,
        ]);

        Producto::create([
            'descripcion' => 'Papas fritas',
            'precio' => 2500,
            'categoria_id' => $snacks->id,
            'proveedor_id' => $proveedor3->id,
        ]);

        Producto::create([
            'descripcion' => 'Detergente 500 g',
            'precio' => 4800,
            'categoria_id' => $aseo->id,
            'proveedor_id' => $proveedor3->id,
        ]);

        Producto::create([
            'descripcion' => 'Jabón de baño',
            'precio' => 3200,
            'categoria_id' => $cuidado->id,
            'proveedor_id' => $proveedor1->id,
        ]);
    }
}
