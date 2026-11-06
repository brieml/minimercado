<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {

            Schema::disableForeignKeyConstraints();
            // TRUNCATE cierra la transacción abierta en MySQL/MariaDB, por eso va antes de beginTransaction().
            Categoria::truncate();
            DB::beginTransaction();

            $datos = [
                ['id' => 1, 'descripcion' => 'Bebidas'],
                ['id' => 2, 'descripcion' => 'Alimentos'],
                ['id' => 3, 'descripcion' => 'Snacks'],
                ['id' => 4, 'descripcion' => 'Aseo'],
                ['id' => 5, 'descripcion' => 'Cuidado personal']
            ];

            // //Insertar los datos
            foreach ($datos as $reg) {
                DB::table('categorias')->insert([
                    'id' => $reg['id'],
                    'descripcion' => $reg['descripcion'],
                    //Fecha y hora de Inserción y Actualización
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }

            // Categoria::insert($datos);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
