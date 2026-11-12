<?php

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\ClienteSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartamentosSeeder;
use Database\Seeders\ProductoSeeder;
use Database\Seeders\ProveedorSeeder;
use Database\Seeders\UsuarioSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

test('DatabaseSeeder siembra las categorías con sus ids', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Categoria::orderBy('id')->pluck('descripcion', 'id')->all())->toBe([
        1 => 'Bebidas',
        2 => 'Alimentos',
        3 => 'Snacks',
        4 => 'Aseo',
        5 => 'Cuidado personal',
    ]);
});

test('DatabaseSeeder siembra los proveedores', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Proveedor::orderBy('id')->get(['id', 'nombre', 'direccion', 'telefono'])->toArray())->toEqual([
        ['id' => 1, 'nombre' => 'Distribuidora La Economía', 'direccion' => 'Sincelejo', 'telefono' => '3001234567'],
        ['id' => 2, 'nombre' => 'Distribuciones Sucre', 'direccion' => 'Sincelejo', 'telefono' => '3012345678'],
        ['id' => 3, 'nombre' => 'Comercializadora Caribe', 'direccion' => 'Sincelejo', 'telefono' => '3023456789'],
    ]);
});

test('DatabaseSeeder siembra los clientes', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Cliente::orderBy('id')->get(['id', 'nombre', 'direccion', 'telefono'])->toArray())->toEqual([
        ['id' => 1, 'nombre' => 'Cliente General', 'direccion' => null, 'telefono' => null],
        ['id' => 2, 'nombre' => 'Juan Pérez', 'direccion' => 'Barrio Centro', 'telefono' => '3001112233'],
        ['id' => 3, 'nombre' => 'María López', 'direccion' => 'Barrio La Palma', 'telefono' => '3002223344'],
    ]);
});

test('DatabaseSeeder siembra los usuarios activos con la contraseña cifrada', function () {
    $this->seed(DatabaseSeeder::class);

    $usuarios = Usuario::orderBy('id')->get();

    expect($usuarios->map(fn (Usuario $usuario) => [
        $usuario->id, $usuario->nombre, $usuario->apellido, $usuario->correo, $usuario->rol, $usuario->estado,
    ])->all())->toEqual([
        [1, 'Administrador', 'Principal', 'admin@minimercado.com', 'Administrador', 'Activo'],
        [2, 'Cajero', 'Principal', 'cajero@minimercado.com', 'Cajero', 'Activo'],
        [3, 'Usuario', 'Inventario', 'inventario@minimercado.com', 'Inventario', 'Activo'],
    ]);

    foreach ($usuarios as $usuario) {
        expect($usuario->password)->not->toBe('12345678')
            ->and(Hash::check('12345678', $usuario->password))->toBeTrue();
    }
});

test('DatabaseSeeder siembra los productos con su categoría, proveedor y si se venden por gramaje', function () {
    $this->seed(DatabaseSeeder::class);

    $productos = Producto::with(['categoria', 'proveedor'])->orderBy('id')->get();

    expect($productos->map(fn (Producto $producto) => [
        $producto->id,
        $producto->descripcion,
        (float) $producto->precio,
        $producto->tiene_gramaje,
        $producto->categoria->descripcion,
        $producto->proveedor->nombre,
    ])->all())->toEqual([
        [1, 'Gaseosa 1.5 Litros', 6500, false, 'Bebidas', 'Distribuidora La Economía'],
        [2, 'Agua 600 ml', 2500, false, 'Bebidas', 'Distribuidora La Economía'],
        [3, 'Arroz 1 Kg', 4500, true, 'Alimentos', 'Distribuciones Sucre'],
        [4, 'Pan tajado', 5500, false, 'Alimentos', 'Distribuciones Sucre'],
        [5, 'Papas fritas', 2500, false, 'Snacks', 'Comercializadora Caribe'],
        [6, 'Detergente 500 g', 4800, true, 'Aseo', 'Comercializadora Caribe'],
        [7, 'Jabón de baño', 3200, false, 'Cuidado personal', 'Distribuidora La Economía'],
    ]);
});

test('los registros sembrados en :dataset llevan fecha de creación y de actualización', function (string $tabla) {
    $this->seed(DatabaseSeeder::class);

    expect(DB::table($tabla)->count())->toBeGreaterThan(0)
        ->and(DB::table($tabla)->whereNull('created_at')->orWhereNull('updated_at')->count())->toBe(0);
})->with(['usuarios', 'categorias', 'proveedores', 'clientes', 'productos']);

test('el seeder de :dataset se puede ejecutar varias veces sin duplicar registros', function (string $seeder, string $modelo, int $esperados, array $requisitos) {
    $this->seed($requisitos);

    $this->seed($seeder);
    $this->seed($seeder);

    expect($modelo::count())->toBe($esperados);
})->with([
    'usuarios' => [UsuarioSeeder::class, Usuario::class, 3, []],
    'categorias' => [CategoriaSeeder::class, Categoria::class, 5, []],
    'proveedores' => [ProveedorSeeder::class, Proveedor::class, 3, []],
    'clientes' => [ClienteSeeder::class, Cliente::class, 3, []],
    'productos' => [ProductoSeeder::class, Producto::class, 7, [CategoriaSeeder::class, ProveedorSeeder::class]],
]);

test('si falla una inserción se revierte la transacción y se relanza la excepción', function () {
    $this->seed([CategoriaSeeder::class, ProveedorSeeder::class]);

    DB::unprepared("CREATE TRIGGER falla_al_insertar_producto BEFORE INSERT ON productos WHEN NEW.id = 4 BEGIN SELECT RAISE(ABORT, 'falla simulada'); END");

    expect(fn () => $this->seed(ProductoSeeder::class))->toThrow(QueryException::class, 'falla simulada')
        ->and(Producto::count())->toBe(0);
});

test('DepartamentosSeeder siembra los departamentos sin duplicarlos', function () {
    if (! class_exists('App\Models\Departamento') || ! Schema::hasTable('departamentos')) {
        $this->markTestSkipped('Falta el modelo App\Models\Departamento o la tabla departamentos.');
    }

    $this->seed(DepartamentosSeeder::class);
    $this->seed(DepartamentosSeeder::class);

    expect(DB::table('departamentos')->count())->toBe(32)
        ->and(DB::table('departamentos')->where('id', 70)->value('nombre'))->toBe('Sucre');
});
