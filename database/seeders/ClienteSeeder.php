<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {

            Schema::disableForeignKeyConstraints();
            // TRUNCATE cierra la transacción abierta en MySQL/MariaDB, por eso va antes de beginTransaction().
            Cliente::truncate();
            DB::beginTransaction();

            $datos = [
                ['id' => 1, 'nombre' => 'Cliente General', 'direccion' => null, 'telefono' => null],
                ['id' => 2, 'nombre' => 'Juan Pérez', 'direccion' => 'Barrio Centro', 'telefono' => '3001112233'],
                ['id' => 3, 'nombre' => 'María López', 'direccion' => 'Barrio La Palma', 'telefono' => '3002223344']
            ];

            // //Insertar los datos
            foreach ($datos as $reg) {
                DB::table('clientes')->insert([
                    'id' => $reg['id'],
                    'nombre' => $reg['nombre'],
                    'direccion' => $reg['direccion'],
                    'telefono' => $reg['telefono'],
                    //Fecha y hora de Inserción y Actualización
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }

            // Cliente::insert($datos);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
