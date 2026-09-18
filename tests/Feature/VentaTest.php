<?php

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Venta;

test('cashiers can view the sales list', function () {
    $cajero = Usuario::factory()->cajero()->create();
    Factura::factory()->has(Venta::factory()->count(2))->create();

    $response = $this->actingAs($cajero)->get(route('ventas.index'));

    $response->assertOk();
});

test('cashiers can register a sale with multiple line items', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $cliente = Cliente::factory()->create();
    $productoUno = Producto::factory()->create(['precio' => 2500]);
    $productoDos = Producto::factory()->create(['precio' => 4500]);

    $response = $this->actingAs($cajero)->post(route('ventas.store'), [
        'fecha' => now()->format('Y-m-d'),
        'cliente_id' => $cliente->id,
        'items' => [
            ['producto_id' => $productoUno->id, 'cantidad' => 3],
            ['producto_id' => $productoDos->id, 'cantidad' => 2],
        ],
    ]);

    $factura = Factura::first();
    $response->assertRedirect(route('ventas.show', $factura));

    expect($factura)->not->toBeNull();
    expect($factura->cliente_id)->toBe($cliente->id);
    expect($factura->usuario_id)->toBe($cajero->id);
    expect($factura->ventas)->toHaveCount(2);
    expect($factura->total())->toEqual(3 * 2500 + 2 * 4500);
});

test('a sale requires at least one product', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $cliente = Cliente::factory()->create();

    $response = $this->actingAs($cajero)->post(route('ventas.store'), [
        'fecha' => now()->format('Y-m-d'),
        'cliente_id' => $cliente->id,
        'items' => [],
    ]);

    $response->assertSessionHasErrors('items');
});

test('a sale can not repeat the same product twice', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create();

    $response = $this->actingAs($cajero)->post(route('ventas.store'), [
        'fecha' => now()->format('Y-m-d'),
        'cliente_id' => $cliente->id,
        'items' => [
            ['producto_id' => $producto->id, 'cantidad' => 1],
            ['producto_id' => $producto->id, 'cantidad' => 2],
        ],
    ]);

    $response->assertSessionHasErrors(['items.0.producto_id', 'items.1.producto_id']);
});

test('updating a sale replaces its line items', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $factura = Factura::factory()->create();
    Venta::factory()->count(2)->create(['factura_id' => $factura->id]);
    $nuevoProducto = Producto::factory()->create(['precio' => 1000]);

    $response = $this->actingAs($cajero)->put(route('ventas.update', $factura), [
        'fecha' => $factura->fecha->format('Y-m-d'),
        'cliente_id' => $factura->cliente_id,
        'items' => [
            ['producto_id' => $nuevoProducto->id, 'cantidad' => 4],
        ],
    ]);

    $response->assertRedirect(route('ventas.show', $factura));
    expect($factura->fresh()->ventas)->toHaveCount(1);
    expect(Venta::withTrashed()->where('factura_id', $factura->id)->count())->toBe(1);
});

test('deleting a sale soft deletes the invoice and its line items', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $factura = Factura::factory()->create();
    $venta = Venta::factory()->create(['factura_id' => $factura->id]);

    $response = $this->actingAs($cajero)->delete(route('ventas.destroy', $factura));

    $response->assertRedirect(route('ventas.index'));
    $this->assertSoftDeleted($factura);
    $this->assertSoftDeleted($venta);
});

test('inventory users can not access the sales module', function () {
    $inventario = Usuario::factory()->inventario()->create();

    $response = $this->actingAs($inventario)->get(route('ventas.index'));

    $response->assertForbidden();
});
