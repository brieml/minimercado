<?php

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->withoutVite();
});

/*
 * Cada módulo: recurso (prefijo de las rutas y de las vistas), nombre de la variable
 * que recibe la vista de edición, modelo y campo (admite relaciones) que se muestra en pantalla.
 */
dataset('modulos', [
    'usuarios' => ['usuarios', 'usuario', Usuario::class, 'correo'],
    'clientes' => ['clientes', 'cliente', Cliente::class, 'nombre'],
    'categorias' => ['categorias', 'categoria', Categoria::class, 'descripcion'],
    'proveedores' => ['proveedores', 'proveedor', Proveedor::class, 'nombre'],
    'productos' => ['productos', 'producto', Producto::class, 'descripcion'],
    'facturas' => ['facturas', 'factura', Factura::class, 'cliente.nombre'],
    'ventas' => ['ventas', 'venta', Venta::class, 'producto.descripcion'],
]);

test('los invitados son redirigidos al login en :dataset', function (string $recurso, string $variable, string $modelo) {
    $registro = $modelo::factory()->create();

    $this->get(route("$recurso.index"))->assertRedirect(route('login'));
    $this->get(route("$recurso.create"))->assertRedirect(route('login'));
    $this->get(route("$recurso.edit", $registro))->assertRedirect(route('login'));
})->with('modulos');

test('el listado de :dataset muestra sus registros', function (string $recurso, string $variable, string $modelo, string $campo) {
    $this->actingAs(Usuario::factory()->create());
    $registro = $modelo::factory()->create();

    $this->get(route("$recurso.index"))
        ->assertOk()
        ->assertViewIs("$recurso.index")
        ->assertViewHas($recurso)
        ->assertSee(data_get($registro, $campo));
})->with('modulos');

test('el formulario de creación de :dataset se muestra con @csrf y la ruta de store', function (string $recurso) {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route("$recurso.create"))
        ->assertOk()
        ->assertViewIs("$recurso.create")
        ->assertSee(route("$recurso.store"), false)
        ->assertSee('name="_token"', false);
})->with('modulos');

test('el formulario de edición de :dataset se muestra con sus datos, @method(PUT) y la ruta de update', function (string $recurso, string $variable, string $modelo, string $campo) {
    $this->actingAs(Usuario::factory()->create());
    $registro = $modelo::factory()->create();

    $this->get(route("$recurso.edit", $registro))
        ->assertOk()
        ->assertViewIs("$recurso.edit")
        ->assertViewHas($variable, fn ($modeloEnVista) => $modeloEnVista->is($registro))
        ->assertSee(route("$recurso.update", $registro), false)
        ->assertSee('name="_method" value="PUT"', false)
        ->assertSee(data_get($registro, $campo));
})->with('modulos');

test('editar un registro inexistente de :dataset responde 404', function (string $recurso) {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route("$recurso.edit", 999999))->assertNotFound();
})->with('modulos');

test('el listado de :dataset incluye el formulario @method(DELETE) por cada registro', function (string $recurso, string $variable, string $modelo) {
    $this->actingAs(Usuario::factory()->create());
    $registro = $modelo::factory()->create();

    $this->get(route("$recurso.index"))
        ->assertSee(route("$recurso.destroy", $registro), false)
        ->assertSee('name="_method" value="DELETE"', false);
})->with('modulos');

test('las rutas de :dataset usan los verbos HTTP del recurso', function (string $recurso) {
    $rutas = Route::getRoutes();

    expect($rutas->getByName("$recurso.index")->methods())->toContain('GET')
        ->and($rutas->getByName("$recurso.create")->methods())->toContain('GET')
        ->and($rutas->getByName("$recurso.store")->methods())->toBe(['POST'])
        ->and($rutas->getByName("$recurso.edit")->methods())->toContain('GET')
        ->and($rutas->getByName("$recurso.update")->methods())->toBe(['PUT', 'PATCH'])
        ->and($rutas->getByName("$recurso.destroy")->methods())->toBe(['DELETE']);
})->with('modulos');

test('la ruta de proveedores usa el parámetro singular {proveedor}', function () {
    expect(Route::getRoutes()->getByName('proveedores.edit')->uri())->toBe('proveedores/{proveedor}/edit');
});

test('el formulario de productos lista las categorías y los proveedores', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create();
    $proveedor = Proveedor::factory()->create();

    $this->get(route('productos.create'))
        ->assertViewHas('categorias')
        ->assertViewHas('proveedores')
        ->assertSee($categoria->descripcion)
        ->assertSee($proveedor->nombre);
});

test('la edición de un producto deja seleccionadas su categoría y su proveedor', function () {
    $this->actingAs(Usuario::factory()->create());
    $producto = Producto::factory()->create();

    $this->get(route('productos.edit', $producto))
        ->assertSee('selected>'.e($producto->categoria->descripcion).'</option>', false)
        ->assertSee('selected>'.e($producto->proveedor->nombre).'</option>', false);
});

test('el formulario de facturas lista los clientes y los usuarios', function () {
    $this->actingAs(Usuario::factory()->create());
    $cliente = Cliente::factory()->create();
    $usuario = Usuario::factory()->create();

    $this->get(route('facturas.create'))
        ->assertViewHas('clientes')
        ->assertViewHas('usuarios')
        ->assertSee($cliente->nombre)
        ->assertSee($usuario->nombre_completo);
});

test('el formulario de ventas lista las facturas y los productos', function () {
    $this->actingAs(Usuario::factory()->create());
    $factura = Factura::factory()->create();
    $producto = Producto::factory()->create();

    $this->get(route('ventas.create'))
        ->assertViewHas('facturas')
        ->assertViewHas('productos')
        ->assertSee('#'.$factura->id)
        ->assertSee($producto->descripcion);
});

test('el formulario de usuarios ofrece los roles y los estados', function () {
    $this->actingAs(Usuario::factory()->create());

    $respuesta = $this->get(route('usuarios.create'))
        ->assertViewHas('roles', Usuario::ROLES)
        ->assertViewHas('estados', Usuario::ESTADOS);

    foreach ([...Usuario::ROLES, ...Usuario::ESTADOS] as $opcion) {
        $respuesta->assertSee('<option value="'.$opcion.'"', false);
    }
});

test('la edición de un usuario no expone su contraseña', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create();

    $this->get(route('usuarios.edit', $usuario))
        ->assertOk()
        ->assertDontSee($usuario->password);
});

test('los listados vacíos muestran un mensaje en lugar de la tabla vacía', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route('productos.index'))->assertSee('No hay productos registrados.');
});

test('los registros eliminados lógicamente no aparecen en el listado', function () {
    $this->actingAs(Usuario::factory()->create());
    $visible = Categoria::factory()->create();
    $eliminada = Categoria::factory()->create();
    $eliminada->delete();

    $this->get(route('categorias.index'))
        ->assertSee($visible->descripcion)
        ->assertDontSee($eliminada->descripcion);
});

test('las páginas autenticadas incluyen el menú hacia todos los módulos y el cierre de sesión', function () {
    $this->actingAs($usuario = Usuario::factory()->create());

    $respuesta = $this->get(route('dashboard'))->assertOk();

    foreach (['usuarios', 'clientes', 'categorias', 'proveedores', 'productos', 'facturas', 'ventas'] as $recurso) {
        $respuesta->assertSee(route("$recurso.index"), false);
    }

    $respuesta->assertSee(route('logout'), false)
        ->assertSee($usuario->nombre_completo)
        ->assertSee($usuario->iniciales);
});

test('el menú marca como activo el módulo que se está visitando', function () {
    $this->actingAs(Usuario::factory()->create());

    $html = $this->get(route('clientes.index'))->getContent();

    expect($html)->toMatch('#<a href="'.preg_quote(route('clientes.index'), '#').'"\s+aria-current="page"#')
        ->not->toMatch('#<a href="'.preg_quote(route('dashboard'), '#').'"\s+aria-current="page"#')
        ->not->toMatch('#<a href="'.preg_quote(route('productos.index'), '#').'"\s+aria-current="page"#');
});
