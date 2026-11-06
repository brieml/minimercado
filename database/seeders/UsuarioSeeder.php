<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {

            Schema::disableForeignKeyConstraints();
            // TRUNCATE cierra la transacción abierta en MySQL/MariaDB, por eso va antes de beginTransaction().
            Usuario::truncate();
            DB::beginTransaction();

            $datos = [
                [
                    'id' => 1,
                    'nombre' => 'Administrador',
                    'apellido' => 'Principal',
                    'correo' => 'admin@minimercado.com',
                    'password' => Hash::make('12345678'),
                    'rol' => 'Administrador',
                    'estado' => 'Activo'
                ],
                [
                    'id' => 2,
                    'nombre' => 'Cajero',
                    'apellido' => 'Principal',
                    'correo' => 'cajero@minimercado.com',
                    'password' => Hash::make('12345678'),
                    'rol' => 'Cajero',
                    'estado' => 'Activo'
                ],
                [
                    'id' => 3,
                    'nombre' => 'Usuario',
                    'apellido' => 'Inventario',
                    'correo' => 'inventario@minimercado.com',
                    'password' => Hash::make('12345678'),
                    'rol' => 'Inventario',
                    'estado' => 'Activo'
                ]
            ];

            // //Insertar los datos
            foreach ($datos as $reg) {
                DB::table('usuarios')->insert([
                    'id' => $reg['id'],
                    'nombre' => $reg['nombre'],
                    'apellido' => $reg['apellido'],
                    'correo' => $reg['correo'],
                    'password' => $reg['password'],
                    'rol' => $reg['rol'],
                    'estado' => $reg['estado'],
                    //Fecha y hora de Inserción y Actualización
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }

            // Usuario::insert($datos);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
