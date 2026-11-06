<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {

            Schema::disableForeignKeyConstraints();
            // TRUNCATE cierra la transacción abierta en MySQL/MariaDB, por eso va antes de beginTransaction().
            Producto::truncate();
            DB::beginTransaction();

            // categoria_id: 1 Bebidas, 2 Alimentos, 3 Snacks, 4 Aseo, 5 Cuidado personal (CategoriaSeeder)
            // proveedor_id: 1 Distribuidora La Economía, 2 Distribuciones Sucre, 3 Comercializadora Caribe (ProveedorSeeder)
            $datos = [
                ['id' => 1, 'descripcion' => 'Gaseosa 1.5 Litros', 'precio' => 6500, 'categoria_id' => 1, 'proveedor_id' => 1],
                ['id' => 2, 'descripcion' => 'Agua 600 ml', 'precio' => 2500, 'categoria_id' => 1, 'proveedor_id' => 1],
                ['id' => 3, 'descripcion' => 'Arroz 1 Kg', 'precio' => 4500, 'categoria_id' => 2, 'proveedor_id' => 2],
                ['id' => 4, 'descripcion' => 'Pan tajado', 'precio' => 5500, 'categoria_id' => 2, 'proveedor_id' => 2],
                ['id' => 5, 'descripcion' => 'Papas fritas', 'precio' => 2500, 'categoria_id' => 3, 'proveedor_id' => 3],
                ['id' => 6, 'descripcion' => 'Detergente 500 g', 'precio' => 4800, 'categoria_id' => 4, 'proveedor_id' => 3],
                ['id' => 7, 'descripcion' => 'Jabón de baño', 'precio' => 3200, 'categoria_id' => 5, 'proveedor_id' => 1]
            ];

            // //Insertar los datos
            foreach ($datos as $reg) {
                DB::table('productos')->insert([
                    'id' => $reg['id'],
                    'descripcion' => $reg['descripcion'],
                    'precio' => $reg['precio'],
                    'categoria_id' => $reg['categoria_id'],
                    'proveedor_id' => $reg['proveedor_id'],
                    //Fecha y hora de Inserción y Actualización
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }

            // Producto::insert($datos);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
