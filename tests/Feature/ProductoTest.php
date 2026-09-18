<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;

test('inventory users can view the products list', function () {
    $inventario = Usuario::factory()->inventario()->create();
    Producto::factory()->count(3)->create();

    $response = $this->actingAs($inventario)->get(route('productos.index'));

    $response->assertOk();
});

test('inventory users can create a product', function () {
    $inventario = Usuario::factory()->inventario()->create();
    $categoria = Categoria::factory()->create();
    $proveedor = Proveedor::factory()->create();

    $response = $this->actingAs($inventario)->post(route('productos.store'), [
        'descripcion' => 'Chocolate en barra',
        'precio' => 3500,
        'categoria_id' => $categoria->id,
        'proveedor_id' => $proveedor->id,
    ]);

    $response->assertRedirect(route('productos.index'));
    $this->assertDatabaseHas('productos', ['descripcion' => 'Chocolate en barra']);
});

test('product requires a valid category and supplier', function () {
    $inventario = Usuario::factory()->inventario()->create();

    $response = $this->actingAs($inventario)->post(route('productos.store'), [
        'descripcion' => 'Producto sin relaciones',
        'precio' => 1000,
        'categoria_id' => 9999,
        'proveedor_id' => 9999,
    ]);

    $response->assertSessionHasErrors(['categoria_id', 'proveedor_id']);
});

test('inventory users can update a product', function () {
    $inventario = Usuario::factory()->inventario()->create();
    $producto = Producto::factory()->create();

    $response = $this->actingAs($inventario)->put(route('productos.update', $producto), [
        'descripcion' => 'Descripción actualizada',
        'precio' => 9999,
        'categoria_id' => $producto->categoria_id,
        'proveedor_id' => $producto->proveedor_id,
    ]);

    $response->assertRedirect(route('productos.index'));
    $this->assertDatabaseHas('productos', ['id' => $producto->id, 'descripcion' => 'Descripción actualizada']);
});

test('inventory users can delete a product', function () {
    $inventario = Usuario::factory()->inventario()->create();
    $producto = Producto::factory()->create();

    $response = $this->actingAs($inventario)->delete(route('productos.destroy', $producto));

    $response->assertRedirect(route('productos.index'));
    $this->assertSoftDeleted($producto);
});

test('cashiers can not access the products module', function () {
    $cajero = Usuario::factory()->cajero()->create();

    $response = $this->actingAs($cajero)->get(route('productos.index'));

    $response->assertForbidden();
});
