<?php

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Lo que debe poder hacer cada rol (la especificación): módulos a los que puede entrar.
 *
 * @return array<string, list<string>>
 */
function permisosPorRol(): array
{
    return [
        'Administrador' => ['productos', 'categorias', 'proveedores', 'clientes', 'facturas', 'ventas', 'usuarios'],
        'Cajero' => ['clientes', 'facturas', 'ventas'],
        'Inventario' => ['productos', 'categorias', 'proveedores'],
    ];
}

function usuarioConRol(string $rol): Usuario
{
    return Usuario::factory()->create(['rol' => $rol]);
}

/**
 * HTML del menú lateral de una página.
 */
function menuLateral(string $html): string
{
    return Str::between($html, '<aside id="sidebar"', '</aside>');
}

dataset('acceso por rol', function () {
    $modulos = ['productos', 'categorias', 'proveedores', 'clientes', 'facturas', 'ventas', 'usuarios'];

    foreach (permisosPorRol() as $rol => $permitidos) {
        foreach ($modulos as $modulo) {
            yield "$rol en $modulo" => [$rol, $modulo, in_array($modulo, $permitidos, true)];
        }
    }
});

test('la tabla de permisos del modelo coincide con la de cada rol', function () {
    expect(Usuario::MODULOS_POR_ROL)->toBe(permisosPorRol());

    foreach (permisosPorRol() as $rol => $modulos) {
        $usuario = new Usuario(['rol' => $rol]);

        foreach (['productos', 'categorias', 'proveedores', 'clientes', 'facturas', 'ventas', 'usuarios'] as $modulo) {
            expect($usuario->puedeAccederA($modulo))->toBe(in_array($modulo, $modulos, true));
        }
    }
});

test('un rol desconocido no tiene acceso a ningún módulo', function () {
    expect((new Usuario(['rol' => 'Invitado']))->puedeAccederA('productos'))->toBeFalse()
        ->and(Usuario::rolesConAccesoA('ventas'))->toBe(['Administrador', 'Cajero'])
        ->and(Usuario::rolesConAccesoA('productos'))->toBe(['Administrador', 'Inventario'])
        ->and(Usuario::rolesConAccesoA('usuarios'))->toBe(['Administrador']);
});

test('cada rol entra solo a los módulos que le corresponden: :dataset', function (string $rol, string $modulo, bool $permitido) {
    $this->actingAs(usuarioConRol($rol));

    $respuesta = $this->get(route("$modulo.index"));

    $permitido ? $respuesta->assertOk() : $respuesta->assertForbidden();
})->with('acceso por rol');

test('el menú lateral de cada rol solo enlaza sus módulos: :dataset', function (string $rol, string $modulo, bool $permitido) {
    $this->actingAs(usuarioConRol($rol));

    $menu = menuLateral($this->get(route('dashboard'))->assertOk()->getContent());

    expect(str_contains($menu, 'href="'.route("$modulo.index").'"'))->toBe($permitido);
})->with('acceso por rol');

test('el menú solo muestra los grupos que tienen algún módulo para el rol', function (string $rol, array $presentes, array $ausentes) {
    $this->actingAs(usuarioConRol($rol));

    $menu = menuLateral($this->get(route('dashboard'))->assertOk()->getContent());

    foreach ($presentes as $grupo) {
        expect($menu)->toContain('>'.$grupo.'</p>');
    }

    foreach ($ausentes as $grupo) {
        expect($menu)->not->toContain('>'.$grupo.'</p>');
    }
})->with([
    'Administrador' => ['Administrador', ['Inventario', 'Ventas', 'Administración'], []],
    'Cajero' => ['Cajero', ['Ventas'], ['Inventario', 'Administración']],
    'Inventario' => ['Inventario', ['Inventario'], ['Ventas', 'Administración']],
]);

test('todos los roles tienen el dashboard y el cierre de sesión en el menú', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));

    $respuesta = $this->get(route('dashboard'))->assertOk();

    expect(menuLateral($respuesta->getContent()))->toContain('href="'.route('dashboard').'"');
    $respuesta->assertSee(route('logout'), false);
})->with(['Administrador', 'Cajero', 'Inventario']);

test('el dashboard cuenta y muestra solo los módulos del rol: :dataset', function (string $rol) {
    $this->actingAs(usuarioConRol($rol));
    Producto::factory()->create();
    Venta::factory()->create();

    $modulos = permisosPorRol()[$rol];
    $respuesta = $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('totales', function ($totales) use ($modulos) {
            $claves = $totales->keys()->all();
            sort($claves);
            sort($modulos);

            return $claves === $modulos;
        });

    foreach (['productos', 'categorias', 'proveedores', 'clientes', 'facturas', 'ventas', 'usuarios'] as $modulo) {
        $enlace = 'href="'.route("$modulo.index").'"';

        in_array($modulo, $modulos, true)
            ? $respuesta->assertSee($enlace, false)
            : $respuesta->assertDontSee($enlace, false);
    }
})->with(['Administrador', 'Cajero', 'Inventario']);

test('el dashboard indica los módulos que tiene el rol', function () {
    $this->actingAs(usuarioConRol('Cajero'));

    $this->get(route('dashboard'))->assertSee('Clientes, Facturas, Ventas');
});

test('la página de acceso denegado explica el motivo y ofrece volver al dashboard', function () {
    $this->actingAs(usuarioConRol('Cajero'));

    $this->get(route('productos.index'))
        ->assertForbidden()
        ->assertSee('Acceso denegado')
        ->assertSee('No tienes permisos para acceder a este módulo.')
        ->assertSee('Cajero')
        ->assertSee(route('dashboard'), false);
});

test('un rol sin acceso no puede crear, modificar ni eliminar en ese módulo', function () {
    $cajero = usuarioConRol('Cajero');
    $producto = Producto::factory()->create(['descripcion' => 'Leche']);
    $usuarioAjeno = Usuario::factory()->create();

    $this->actingAs($cajero);

    $this->post(route('productos.store'), ['descripcion' => 'Nuevo', 'precio' => 1000])->assertForbidden();
    $this->put(route('productos.update', $producto), ['descripcion' => 'Cambiado', 'precio' => 1])->assertForbidden();
    $this->delete(route('productos.destroy', $producto))->assertForbidden();
    $this->post(route('categorias.store'), ['descripcion' => 'Nueva'])->assertForbidden();
    $this->post(route('proveedores.store'), ['nombre' => 'Nuevo'])->assertForbidden();
    $this->delete(route('usuarios.destroy', $usuarioAjeno))->assertForbidden();

    expect($producto->fresh()->descripcion)->toBe('Leche')
        ->and($producto->fresh()->trashed())->toBeFalse()
        ->and(Producto::count())->toBe(1)
        ->and(Categoria::count())->toBe(1)
        ->and(Proveedor::count())->toBe(1)
        ->and($usuarioAjeno->fresh()->trashed())->toBeFalse();
});

test('el usuario de inventario no puede registrar ventas ni clientes ni tocar facturas', function () {
    $factura = Factura::factory()->create();
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create();
    $clientesAntes = Cliente::count();

    $this->actingAs(usuarioConRol('Inventario'));

    $this->post(route('ventas.store'), [
        'cliente_id' => $cliente->id,
        'lineas' => [['producto_id' => $producto->id, 'cantidad' => '1']],
    ])->assertForbidden();
    $this->post(route('clientes.store'), ['nombre' => 'Nuevo cliente'])->assertForbidden();
    $this->delete(route('facturas.destroy', $factura))->assertForbidden();

    expect(Venta::count())->toBe(0)
        ->and(Factura::count())->toBe(1)
        ->and(Cliente::count())->toBe($clientesAntes)
        ->and($factura->fresh()->trashed())->toBeFalse();
});

test('el cajero registra clientes y consulta las facturas', function () {
    $this->actingAs(usuarioConRol('Cajero'));
    $factura = Factura::factory()->create();

    $this->post(route('clientes.store'), ['nombre' => 'María López'])->assertRedirect(route('clientes.index'));
    $this->get(route('facturas.index'))->assertOk()->assertSee((string) $factura->id);
    $this->get(route('facturas.show', $factura))->assertOk();

    expect(Cliente::where('nombre', 'María López')->exists())->toBeTrue();
});

test('el usuario de inventario administra productos, categorías y proveedores', function () {
    $this->actingAs(usuarioConRol('Inventario'));
    $categoria = Categoria::factory()->create();
    $proveedor = Proveedor::factory()->create();

    $this->post(route('productos.store'), [
        'descripcion' => 'Arroz',
        'precio' => '4500',
        'tiene_gramaje' => '1',
        'categoria_id' => $categoria->id,
        'proveedor_id' => $proveedor->id,
    ])->assertRedirect(route('productos.index'));
    $this->post(route('categorias.store'), ['descripcion' => 'Granos'])->assertRedirect(route('categorias.index'));
    $this->post(route('proveedores.store'), ['nombre' => 'Distribuidora Sucre'])->assertRedirect(route('proveedores.index'));

    expect(Producto::where('descripcion', 'Arroz')->exists())->toBeTrue()
        ->and(Categoria::where('descripcion', 'Granos')->exists())->toBeTrue()
        ->and(Proveedor::where('nombre', 'Distribuidora Sucre')->exists())->toBeTrue();
});

test('el administrador entra a todos los módulos y gestiona usuarios', function () {
    $this->actingAs(usuarioConRol('Administrador'));

    $this->post(route('usuarios.store'), [
        'nombre' => 'Laura',
        'apellido' => 'Gómez',
        'correo' => 'laura@minimercado.test',
        'password' => 'secreto123',
        'rol' => 'Cajero',
        'estado' => 'Activo',
    ])->assertRedirect(route('usuarios.index'));

    expect(Usuario::where('correo', 'laura@minimercado.test')->value('rol'))->toBe('Cajero');
});

test('los enlaces a módulos que el rol no puede abrir se muestran como texto', function () {
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $cajeroQueAtendio = usuarioConRol('Cajero');
    $producto = Producto::factory()->create(['descripcion' => 'Arroz Diana']);
    $factura = Factura::factory()->create(['cliente_id' => $cliente->id, 'usuario_id' => $cajeroQueAtendio->id]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $producto->id]);

    $this->actingAs($cajeroQueAtendio)
        ->get(route('facturas.show', $factura))
        ->assertOk()
        ->assertSee('Arroz Diana')
        ->assertSee(route('clientes.show', $cliente), false)
        ->assertDontSee(route('productos.show', $producto), false)
        ->assertDontSee(route('usuarios.show', $cajeroQueAtendio), false);

    $this->actingAs(usuarioConRol('Administrador'))
        ->get(route('facturas.show', $factura))
        ->assertSee(route('productos.show', $producto), false)
        ->assertSee(route('usuarios.show', $cajeroQueAtendio), false);
});
