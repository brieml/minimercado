<?php

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Datos válidos para editar una factura. El cliente y el usuario solo se crean
 * cuando la prueba no los sobrescribe.
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

test('los invitados no pueden modificar ni eliminar facturas', function () {
    $factura = Factura::factory()->create(['fecha' => '2026-10-01']);

    $this->put(route('facturas.update', $factura), datosValidosDeFactura())->assertRedirect(route('login'));
    $this->delete(route('facturas.destroy', $factura))->assertRedirect(route('login'));

    expect($factura->fresh()->fecha->toDateString())->toBe('2026-10-01')
        ->and($factura->fresh()->trashed())->toBeFalse();
});

test('el listado muestra cada factura con su total y sus acciones, pero no ofrece crear facturas', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create(['fecha' => '2026-11-03']);
    $producto = Producto::factory()->create(['precio' => 1500]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

    $this->get(route('facturas.index'))
        ->assertOk()
        ->assertSee('03/11/2026')
        ->assertSee('$ 3.000,00', false)
        ->assertSee(route('facturas.show', $factura), false)
        ->assertSee(route('facturas.edit', $factura), false)
        ->assertSee(route('facturas.destroy', $factura), false)
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee('Se generan automáticamente al registrar una venta')
        ->assertDontSee('Nueva Factura')
        ->assertDontSee('/facturas/create', false);
});

test('el listado calcula el total de todas las facturas sin una consulta por cada una', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    Factura::factory()->count(5)->create()->each(
        fn (Factura $factura) => Venta::factory()->count(2)->create(['factura_id' => $factura->id]),
    );

    DB::enableQueryLog();
    $this->get(route('facturas.index'))->assertOk();
    $consultas = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Sesión y usuario, facturas, y una consulta por cada relación cargada: no crece con el número de facturas.
    expect($consultas)->toBeLessThanOrEqual(8);
});

test('muestra el detalle de una factura con sus productos, subtotales y total', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $cajero = Usuario::factory()->create(['nombre' => 'Laura', 'apellido' => 'Gómez']);
    $factura = Factura::factory()->create(['fecha' => '2026-11-03', 'cliente_id' => $cliente->id, 'usuario_id' => $cajero->id]);
    $arroz = Producto::factory()->conGramaje()->create(['descripcion' => 'Arroz Diana 500 g', 'precio' => 2500.50]);
    $leche = Producto::factory()->create(['descripcion' => 'Leche entera 1 L', 'precio' => 1000]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $arroz->id, 'cantidad' => 1.5]);
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $leche->id, 'cantidad' => 3]);

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
        ->assertSee('<td class="whitespace-nowrap">1,5</td>', false)
        ->assertSee('<td class="whitespace-nowrap">3</td>', false)
        ->assertSee('$ 2.500,50', false)
        ->assertSee('$ 3.750,75', false)
        ->assertSee('$ 3.000,00', false)
        ->assertSee('$ 6.750,75', false)
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
    Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => Producto::factory()->conGramaje()->create(['precio' => 100])->id, 'cantidad' => 0.5]);

    expect($venta->fresh()->subtotal)->toBe(3752.25)
        ->and($factura->fresh()->total)->toBe(3802.25)
        ->and(Factura::factory()->create()->total)->toBe(0.0);
});

test('el formulario de edición trae los datos de la factura y no ofrece valores por defecto', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create(['fecha' => '2026-10-15']);

    $this->get(route('facturas.edit', $factura))
        ->assertOk()
        ->assertViewIs('facturas.edit')
        ->assertSee('Editar Factura #'.$factura->id)
        ->assertSee('value="2026-10-15"', false)
        ->assertSee('<option value="'.$factura->cliente_id.'" selected>', false)
        ->assertSee('<option value="'.$factura->usuario_id.'" selected>', false)
        ->assertSee('name="_method" value="PUT"', false);
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

test('acepta la fecha de hoy y una fecha pasada al actualizar', function (string $cuando) {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create(['fecha' => '2026-10-01']);
    $fecha = now()->modify($cuando)->toDateString();

    $this->put(route('facturas.update', $factura), datosValidosDeFactura(['fecha' => $fecha]))
        ->assertSessionHasNoErrors();

    expect($factura->fresh()->fecha->toDateString())->toBe($fecha);
})->with(['today', '-1 day', '-1 year']);

test('rechaza datos inválidos al actualizar una factura', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create(['fecha' => '2026-10-01']);

    $this->put(route('facturas.update', $factura), datosValidosDeFactura($cambios))->assertSessionHasErrors($campo);

    expect($factura->fresh()->fecha->toDateString())->toBe('2026-10-01');
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
    $factura = Factura::factory()->create();
    $clienteEliminado = Cliente::factory()->create();
    $usuarioEliminado = Usuario::factory()->create();
    $clienteEliminado->delete();
    $usuarioEliminado->delete();

    $this->put(route('facturas.update', $factura), datosValidosDeFactura([
        'cliente_id' => $clienteEliminado->id,
        'usuario_id' => $usuarioEliminado->id,
    ]))->assertSessionHasErrors(['cliente_id', 'usuario_id']);
});

test('muestra los errores y conserva lo escrito cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();
    $datos = datosValidosDeFactura(['fecha' => '2999-12-31']);

    $this->from(route('facturas.edit', $factura))
        ->followingRedirects()
        ->put(route('facturas.update', $factura), $datos)
        ->assertOk()
        ->assertViewIs('facturas.edit')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('La fecha de la factura no puede ser posterior a hoy.')
        ->assertSee('value="2999-12-31"', false)
        ->assertSee('<option value="'.$datos['cliente_id'].'" selected>', false)
        ->assertSee('<option value="'.$datos['usuario_id'].'" selected>', false);

    $this->from(route('facturas.edit', $factura))
        ->followingRedirects()
        ->put(route('facturas.update', $factura), datosValidosDeFactura(['fecha' => '']))
        ->assertSee('El campo fecha es obligatorio.');
});

test('elimina la factura junto con sus ventas (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();
    $ventas = Venta::factory()->count(2)->create(['factura_id' => $factura->id]);
    $ventaDeOtraFactura = Venta::factory()->create();

    $this->delete(route('facturas.destroy', $factura))
        ->assertRedirect(route('facturas.index'))
        ->assertSessionHas('success', 'Factura eliminada correctamente.');

    $this->assertSoftDeleted($factura);
    $ventas->each(fn (Venta $venta) => $this->assertSoftDeleted($venta));
    $this->assertNotSoftDeleted($ventaDeOtraFactura);
});

test('una factura sin ventas también se puede eliminar', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();

    $this->delete(route('facturas.destroy', $factura))->assertSessionHas('success', 'Factura eliminada correctamente.');

    $this->assertSoftDeleted($factura);
});

test('el listado ya no muestra la factura eliminada', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $eliminada = Factura::factory()->create();
    $visible = Factura::factory()->create();

    $this->delete(route('facturas.destroy', $eliminada));

    $this->get(route('facturas.index'))
        ->assertSee(route('facturas.show', $visible), false)
        ->assertDontSee(route('facturas.show', $eliminada), false);
});

test('si falla al eliminar no se borra ni la factura ni sus ventas', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $factura = Factura::factory()->create();
    $venta = Venta::factory()->create(['factura_id' => $factura->id]);

    Factura::deleting(fn () => throw new RuntimeException('falla simulada'));

    $this->followingRedirects()
        ->delete(route('facturas.destroy', $factura))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se pudo eliminar la factura.');

    $this->assertNotSoftDeleted($factura);
    $this->assertNotSoftDeleted($venta);
});

test('mostrar, actualizar o eliminar una factura inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->get(route('facturas.show', 999999))->assertNotFound();
    $this->put(route('facturas.update', 999999), datosValidosDeFactura())->assertNotFound();
    $this->delete(route('facturas.destroy', 999999))->assertNotFound();
});
