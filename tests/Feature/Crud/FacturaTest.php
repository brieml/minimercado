<?php

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Venta;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Datos válidos para el formulario de facturas. El cliente y el usuario solo
 * se crean cuando la prueba no los sobrescribe.
 *
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosValidosDeFactura(array $cambios = []): array
{
    return array_merge([
        'fecha' => '2026-11-03',
        'cliente_id' => $cambios['cliente_id'] ?? Cliente::factory()->create()->id,
        'usuario_id' => $cambios['usuario_id'] ?? Usuario::factory()->create()->id,
    ], $cambios);
}

test('los invitados no pueden crear, actualizar ni eliminar facturas', function () {
    $factura = Factura::factory()->create(['fecha' => '2026-10-01']);

    $this->post(route('facturas.store'), datosValidosDeFactura())->assertRedirect(route('login'));
    $this->put(route('facturas.update', $factura), datosValidosDeFactura())->assertRedirect(route('login'));
    $this->delete(route('facturas.destroy', $factura))->assertRedirect(route('login'));

    expect(Factura::count())->toBe(1)
        ->and($factura->fresh()->fecha->toDateString())->toBe('2026-10-01')
        ->and($factura->fresh()->trashed())->toBeFalse();
});

test('crea una factura y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $datos = datosValidosDeFactura();

    $this->post(route('facturas.store'), $datos)
        ->assertRedirect(route('facturas.index'))
        ->assertSessionHas('success', 'Factura creada correctamente.');

    $factura = Factura::firstOrFail();

    expect($factura->fecha->toDateString())->toBe('2026-11-03')
        ->and($factura->cliente_id)->toBe($datos['cliente_id'])
        ->and($factura->usuario_id)->toBe($datos['usuario_id']);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->followingRedirects()
        ->post(route('facturas.store'), datosValidosDeFactura())
        ->assertOk()
        ->assertViewIs('facturas.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Factura creada correctamente.')
        ->assertSee('03/11/2026');
});

test('acepta la fecha de hoy y una fecha pasada', function (string $cuando) {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('facturas.store'), datosValidosDeFactura(['fecha' => now()->modify($cuando)->toDateString()]))
        ->assertSessionHasNoErrors();

    expect(Factura::count())->toBe(1);
})->with(['today', '-1 day', '-1 year']);

test('rechaza datos inválidos al crear una factura', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('facturas.store'), datosValidosDeFactura($cambios))->assertSessionHasErrors($campo);

    expect(Factura::count())->toBe(0);
})->with([
    'sin fecha' => [['fecha' => ''], 'fecha'],
    'fecha con formato inválido' => [['fecha' => 'no-es-una-fecha'], 'fecha'],
    'fecha futura' => [['fecha' => '2999-12-31'], 'fecha'],
    'sin cliente' => [['cliente_id' => ''], 'cliente_id'],
    'cliente inexistente' => [['cliente_id' => 999999], 'cliente_id'],
    'sin usuario' => [['usuario_id' => ''], 'usuario_id'],
    'usuario inexistente' => [['usuario_id' => 999999], 'usuario_id'],
]);

test('no acepta un cliente ni un usuario que ya fueron eliminados', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $clienteEliminado = Cliente::factory()->create();
    $usuarioEliminado = Usuario::factory()->create();
    $clienteEliminado->delete();
    $usuarioEliminado->delete();

    $this->post(route('facturas.store'), datosValidosDeFactura([
        'cliente_id' => $clienteEliminado->id,
        'usuario_id' => $usuarioEliminado->id,
    ]))->assertSessionHasErrors(['cliente_id', 'usuario_id']);

    expect(Factura::count())->toBe(0);
});

test('muestra los errores y conserva lo escrito cuando falla la validación al crear', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $datos = datosValidosDeFactura(['fecha' => '2999-12-31']);

    $this->from(route('facturas.create'))
        ->followingRedirects()
        ->post(route('facturas.store'), $datos)
        ->assertOk()
        ->assertViewIs('facturas.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('La fecha de la factura no puede ser posterior a hoy.')
        ->assertSee('value="2999-12-31"', false)
        ->assertSee('<option value="'.$datos['cliente_id'].'" selected>', false)
        ->assertSee('<option value="'.$datos['usuario_id'].'" selected>', false);
});

test('el formulario de creación propone la fecha de hoy y al usuario que está atendiendo', function () {
    $this->actingAs($actual = Usuario::factory()->administrador()->create());

    $this->get(route('facturas.create'))
        ->assertOk()
        ->assertSee('value="'.now()->format('Y-m-d').'"', false)
        ->assertSee('<option value="'.$actual->id.'" selected>', false);
});

test('muestra el detalle de una factura con sus productos, subtotales y total', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $cajero = Usuario::factory()->create(['nombre' => 'Laura', 'apellido' => 'Gómez']);
    $factura = Factura::factory()->create(['fecha' => '2026-11-03', 'cliente_id' => $cliente->id, 'usuario_id' => $cajero->id]);
    $arroz = Producto::factory()->create(['descripcion' => 'Arroz Diana 500 g', 'precio' => 2500.50]);
    $leche = Producto::factory()->create(['descripcion' => 'Leche entera 1 L', 'precio' => 1000]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $arroz->id, 'cantidad' => 2]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $leche->id, 'cantidad' => 1.5]);

    $this->get(route('facturas.show', $factura))
        ->assertOk()
        ->assertViewIs('facturas.show')
        ->assertViewHas('factura', fn ($vista) => $vista->is($factura)
            && $vista->relationLoaded('ventas')
            && $vista->ventas->every(fn ($venta) => $venta->relationLoaded('producto')))
        ->assertSee('Factura #'.$factura->id)
        ->assertSee('03/11/2026')
        ->assertSee('Juan Pérez')
        ->assertSee('Laura Gómez')
        ->assertSee('Arroz Diana 500 g')
        ->assertSee('Leche entera 1 L')
        ->assertSee('$ 2.500,50', false)
        ->assertSee('$ 5.001,00', false)
        ->assertSee('$ 1.500,00', false)
        ->assertSee('$ 6.501,00', false)
        ->assertSee(route('clientes.show', $cliente), false)
        ->assertSee(route('usuarios.show', $cajero), false)
        ->assertSee(route('productos.show', $arroz), false);
});

test('el detalle de una factura sin ventas lo indica y muestra el total en cero', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();

    $this->get(route('facturas.show', $factura))
        ->assertOk()
        ->assertSee('Esta factura todavía no tiene productos.')
        ->assertSee('$ 0,00', false);
});

test('calcula el subtotal de cada venta y el total de la factura', function () {
    $factura = Factura::factory()->create();
    $producto = Producto::factory()->create(['precio' => 1250.75]);
    $venta = Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $producto->id, 'cantidad' => 3]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => Producto::factory()->create(['precio' => 100])->id, 'cantidad' => 0.5]);

    expect($venta->fresh()->subtotal)->toBe(3752.25)
        ->and($factura->fresh()->total)->toBe(3802.25)
        ->and(Factura::factory()->create()->total)->toBe(0.0);
});

test('el listado ofrece el enlace al detalle de cada factura', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();

    $this->get(route('facturas.index'))->assertSee(route('facturas.show', $factura), false);
});

test('actualiza una factura y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create(['fecha' => '2026-10-01']);
    $datos = datosValidosDeFactura(['fecha' => '2026-10-15']);

    $this->put(route('facturas.update', $factura), $datos)
        ->assertRedirect(route('facturas.index'))
        ->assertSessionHas('success', 'Factura actualizada correctamente.');

    $factura->refresh();

    expect($factura->fecha->toDateString())->toBe('2026-10-15')
        ->and($factura->cliente_id)->toBe($datos['cliente_id'])
        ->and($factura->usuario_id)->toBe($datos['usuario_id']);
});

test('rechaza datos inválidos al actualizar una factura', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create(['fecha' => '2026-10-01']);

    $this->put(route('facturas.update', $factura), datosValidosDeFactura($cambios))->assertSessionHasErrors($campo);

    expect($factura->fresh()->fecha->toDateString())->toBe('2026-10-01');
})->with([
    'sin fecha' => [['fecha' => ''], 'fecha'],
    'fecha futura' => [['fecha' => '2999-12-31'], 'fecha'],
    'cliente inexistente' => [['cliente_id' => 999999], 'cliente_id'],
    'usuario inexistente' => [['usuario_id' => 999999], 'usuario_id'],
]);

test('muestra los errores cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();

    $this->from(route('facturas.edit', $factura))
        ->followingRedirects()
        ->put(route('facturas.update', $factura), datosValidosDeFactura(['fecha' => '']))
        ->assertOk()
        ->assertViewIs('facturas.edit')
        ->assertSee('El campo fecha es obligatorio.');
});

test('elimina una factura (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();

    $this->delete(route('facturas.destroy', $factura))
        ->assertRedirect(route('facturas.index'))
        ->assertSessionHas('success', 'Factura eliminada correctamente.');

    $this->assertSoftDeleted($factura);
});

test('no elimina una factura que tiene ventas y avisa con un mensaje de error', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();
    Venta::factory()->create(['factura_id' => $factura->id]);

    $this->followingRedirects()
        ->delete(route('facturas.destroy', $factura))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se puede eliminar la factura porque tiene ventas relacionadas.');

    $this->assertNotSoftDeleted($factura);
});

test('mostrar, actualizar o eliminar una factura inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->get(route('facturas.show', 999999))->assertNotFound();
    $this->put(route('facturas.update', 999999), datosValidosDeFactura())->assertNotFound();
    $this->delete(route('facturas.destroy', 999999))->assertNotFound();
});
