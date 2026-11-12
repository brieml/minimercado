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
 * Datos válidos para el formulario de ventas. La factura y el producto solo
 * se crean cuando la prueba no los sobrescribe.
 *
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosValidosDeVenta(array $cambios = []): array
{
    return array_merge([
        'factura_id' => $cambios['factura_id'] ?? Factura::factory()->create()->id,
        'producto_id' => $cambios['producto_id'] ?? Producto::factory()->create()->id,
        'cantidad' => '2.500',
    ], $cambios);
}

test('los invitados no pueden crear, actualizar ni eliminar ventas', function () {
    $venta = Venta::factory()->create(['cantidad' => 4]);

    $this->post(route('ventas.store'), datosValidosDeVenta())->assertRedirect(route('login'));
    $this->put(route('ventas.update', $venta), datosValidosDeVenta())->assertRedirect(route('login'));
    $this->delete(route('ventas.destroy', $venta))->assertRedirect(route('login'));

    expect(Venta::count())->toBe(1)
        ->and((float) $venta->fresh()->cantidad)->toBe(4.0)
        ->and($venta->fresh()->trashed())->toBeFalse();
});

test('crea una venta y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $datos = datosValidosDeVenta();

    $this->post(route('ventas.store'), $datos)
        ->assertRedirect(route('ventas.index'))
        ->assertSessionHas('success', 'Venta creada correctamente.');

    $venta = Venta::firstOrFail();

    expect($venta->factura_id)->toBe($datos['factura_id'])
        ->and($venta->producto_id)->toBe($datos['producto_id'])
        ->and((float) $venta->cantidad)->toBe(2.5);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->followingRedirects()
        ->post(route('ventas.store'), datosValidosDeVenta())
        ->assertOk()
        ->assertViewIs('ventas.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Venta creada correctamente.')
        ->assertSee('2,500');
});

test('acepta cantidades enteras, con hasta tres decimales y el máximo que cabe en la columna', function (string $cantidad) {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('ventas.store'), datosValidosDeVenta(['cantidad' => $cantidad]))
        ->assertSessionHasNoErrors();

    expect(Venta::count())->toBe(1)
        ->and((float) Venta::first()->cantidad)->toBe((float) $cantidad);
})->with(['1', '0.001', '2.5', '10.250', '9999999.999']);

test('rechaza datos inválidos al crear una venta', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('ventas.store'), datosValidosDeVenta($cambios))->assertSessionHasErrors($campo);

    expect(Venta::count())->toBe(0);
})->with([
    'sin factura' => [['factura_id' => ''], 'factura_id'],
    'factura inexistente' => [['factura_id' => 999999], 'factura_id'],
    'sin producto' => [['producto_id' => ''], 'producto_id'],
    'producto inexistente' => [['producto_id' => 999999], 'producto_id'],
    'sin cantidad' => [['cantidad' => ''], 'cantidad'],
    'cantidad no numérica' => [['cantidad' => 'abc'], 'cantidad'],
    'cantidad en cero' => [['cantidad' => '0'], 'cantidad'],
    'cantidad negativa' => [['cantidad' => '-1'], 'cantidad'],
    'cantidad con cuatro decimales' => [['cantidad' => '1.2345'], 'cantidad'],
    'cantidad por encima del máximo' => [['cantidad' => '10000000'], 'cantidad'],
]);

test('explica con un mensaje claro que la cantidad no admite más de tres decimales', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('ventas.store'), datosValidosDeVenta(['cantidad' => '1.2345']))
        ->assertSessionHasErrors(['cantidad' => 'La cantidad no puede tener más de 3 decimales.']);
});

test('no acepta una factura ni un producto que ya fueron eliminados', function () {
    $this->actingAs(Usuario::factory()->create());
    $facturaEliminada = Factura::factory()->create();
    $productoEliminado = Producto::factory()->create();
    $facturaEliminada->delete();
    $productoEliminado->delete();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'factura_id' => $facturaEliminada->id,
        'producto_id' => $productoEliminado->id,
    ]))->assertSessionHasErrors(['factura_id', 'producto_id']);

    expect(Venta::count())->toBe(0);
});

test('muestra los errores y conserva lo escrito cuando falla la validación al crear', function () {
    $this->actingAs(Usuario::factory()->create());
    $datos = datosValidosDeVenta(['cantidad' => '0']);

    $this->from(route('ventas.create'))
        ->followingRedirects()
        ->post(route('ventas.store'), $datos)
        ->assertOk()
        ->assertViewIs('ventas.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El campo cantidad debe ser al menos 0.001.')
        ->assertSee('value="0"', false)
        ->assertSee('<option value="'.$datos['factura_id'].'" selected>', false)
        ->assertSee('<option value="'.$datos['producto_id'].'" selected>', false);
});

test('pide elegir factura y producto cuando se envían vacíos', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->from(route('ventas.create'))
        ->followingRedirects()
        ->post(route('ventas.store'), datosValidosDeVenta(['factura_id' => '', 'producto_id' => '']))
        ->assertOk()
        ->assertSee('El campo factura es obligatorio.')
        ->assertSee('El campo producto es obligatorio.');
});

test('muestra el detalle de una venta con su factura, cliente, producto y subtotal', function () {
    $this->actingAs(Usuario::factory()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $factura = Factura::factory()->create(['fecha' => '2026-11-03', 'cliente_id' => $cliente->id]);
    $producto = Producto::factory()->create(['descripcion' => 'Arroz Diana 500 g', 'precio' => 2500.50]);
    $venta = Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

    $this->get(route('ventas.show', $venta))
        ->assertOk()
        ->assertViewIs('ventas.show')
        ->assertViewHas('venta', fn ($vista) => $vista->is($venta)
            && $vista->relationLoaded('factura')
            && $vista->relationLoaded('producto'))
        ->assertSee('#'.$factura->id.' · 03/11/2026')
        ->assertSee('Juan Pérez')
        ->assertSee('Arroz Diana 500 g')
        ->assertSee('2,000')
        ->assertSee('$ 2.500,50', false)
        ->assertSee('$ 5.001,00', false)
        ->assertSee(route('facturas.show', $factura), false)
        ->assertSee(route('clientes.show', $cliente), false)
        ->assertSee(route('productos.show', $producto), false);
});

test('el listado ofrece el enlace al detalle de cada venta', function () {
    $this->actingAs(Usuario::factory()->create());
    $venta = Venta::factory()->create();

    $this->get(route('ventas.index'))->assertSee(route('ventas.show', $venta), false);
});

test('actualiza una venta y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $venta = Venta::factory()->create(['cantidad' => 1]);
    $datos = datosValidosDeVenta(['cantidad' => '7.250']);

    $this->put(route('ventas.update', $venta), $datos)
        ->assertRedirect(route('ventas.index'))
        ->assertSessionHas('success', 'Venta actualizada correctamente.');

    $venta->refresh();

    expect((float) $venta->cantidad)->toBe(7.25)
        ->and($venta->factura_id)->toBe($datos['factura_id'])
        ->and($venta->producto_id)->toBe($datos['producto_id']);
});

test('rechaza datos inválidos al actualizar una venta', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->create());
    $venta = Venta::factory()->create(['cantidad' => 3]);

    $this->put(route('ventas.update', $venta), datosValidosDeVenta($cambios))->assertSessionHasErrors($campo);

    expect((float) $venta->fresh()->cantidad)->toBe(3.0);
})->with([
    'sin cantidad' => [['cantidad' => ''], 'cantidad'],
    'cantidad negativa' => [['cantidad' => '-1'], 'cantidad'],
    'factura inexistente' => [['factura_id' => 999999], 'factura_id'],
    'producto inexistente' => [['producto_id' => 999999], 'producto_id'],
]);

test('muestra los errores cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->create());
    $venta = Venta::factory()->create();

    $this->from(route('ventas.edit', $venta))
        ->followingRedirects()
        ->put(route('ventas.update', $venta), datosValidosDeVenta(['cantidad' => '']))
        ->assertOk()
        ->assertViewIs('ventas.edit')
        ->assertSee('El campo cantidad es obligatorio.');
});

test('elimina una venta (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $venta = Venta::factory()->create();

    $this->delete(route('ventas.destroy', $venta))
        ->assertRedirect(route('ventas.index'))
        ->assertSessionHas('success', 'Venta eliminada correctamente.');

    $this->assertSoftDeleted($venta);
});

test('al eliminar su única venta, la factura y el producto vuelven a poder eliminarse', function () {
    $this->actingAs(Usuario::factory()->create());
    $venta = Venta::factory()->create();

    $this->delete(route('facturas.destroy', $venta->factura_id))->assertSessionHas('error');
    $this->delete(route('productos.destroy', $venta->producto_id))->assertSessionHas('error');

    $this->delete(route('ventas.destroy', $venta));

    $this->delete(route('facturas.destroy', $venta->factura_id))->assertSessionHas('success');
    $this->delete(route('productos.destroy', $venta->producto_id))->assertSessionHas('success');
});

test('mostrar, actualizar o eliminar una venta inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route('ventas.show', 999999))->assertNotFound();
    $this->put(route('ventas.update', 999999), datosValidosDeVenta())->assertNotFound();
    $this->delete(route('ventas.destroy', 999999))->assertNotFound();
});
