<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {

            Schema::disableForeignKeyConstraints();
            // TRUNCATE cierra la transacción abierta en MySQL/MariaDB, por eso va antes de beginTransaction().
            Proveedor::truncate();
            DB::beginTransaction();

            $datos = [
                ['id' => 1, 'nombre' => 'Distribuidora La Economía', 'direccion' => 'Sincelejo', 'telefono' => '3001234567'],
                ['id' => 2, 'nombre' => 'Distribuciones Sucre', 'direccion' => 'Sincelejo', 'telefono' => '3012345678'],
                ['id' => 3, 'nombre' => 'Comercializadora Caribe', 'direccion' => 'Sincelejo', 'telefono' => '3023456789']
            ];

            // //Insertar los datos
            foreach ($datos as $reg) {
                DB::table('proveedores')->insert([
                    'id' => $reg['id'],
                    'nombre' => $reg['nombre'],
                    'direccion' => $reg['direccion'],
                    'telefono' => $reg['telefono'],
                    //Fecha y hora de Inserción y Actualización
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }

            // Proveedor::insert($datos);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
