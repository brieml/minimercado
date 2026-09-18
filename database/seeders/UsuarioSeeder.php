<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::create([
            'nombre' => 'Administrador',
            'apellido' => 'Principal',
            'correo' => 'admin@minimercado.com',
            'password' => Hash::make('12345678'),
            'rol' => 'Administrador',
            'estado' => 'Activo',
        ]);

        Usuario::create([
            'nombre' => 'Cajero',
            'apellido' => 'Principal',
            'correo' => 'cajero@minimercado.com',
            'password' => Hash::make('12345678'),
            'rol' => 'Cajero',
            'estado' => 'Activo',
        ]);

        Usuario::create([
            'nombre' => 'Usuario',
            'apellido' => 'Inventario',
            'correo' => 'inventario@minimercado.com',
            'password' => Hash::make('12345678'),
            'rol' => 'Inventario',
            'estado' => 'Activo',
        ]);
    }
}
