<?php

use App\Models\Proveedor;
use App\Models\Usuario;

test('inventory users can view the suppliers list', function () {
    $inventario = Usuario::factory()->inventario()->create();
    Proveedor::factory()->count(3)->create();

    $response = $this->actingAs($inventario)->get(route('proveedores.index'));

    $response->assertOk();
});

test('inventory users can create a supplier', function () {
    $inventario = Usuario::factory()->inventario()->create();

    $response = $this->actingAs($inventario)->post(route('proveedores.store'), [
        'nombre' => 'Distribuidora Nueva',
        'direccion' => 'Sincelejo',
        'telefono' => '3001112233',
    ]);

    $response->assertRedirect(route('proveedores.index'));
    $this->assertDatabaseHas('proveedores', ['nombre' => 'Distribuidora Nueva']);
});

test('inventory users can update a supplier', function () {
    $inventario = Usuario::factory()->inventario()->create();
    $proveedor = Proveedor::factory()->create();

    $response = $this->actingAs($inventario)->put(route('proveedores.update', $proveedor), [
        'nombre' => 'Nombre Actualizado',
    ]);

    $response->assertRedirect(route('proveedores.index'));
    $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'nombre' => 'Nombre Actualizado']);
});

test('inventory users can delete a supplier', function () {
    $inventario = Usuario::factory()->inventario()->create();
    $proveedor = Proveedor::factory()->create();

    $response = $this->actingAs($inventario)->delete(route('proveedores.destroy', $proveedor));

    $response->assertRedirect(route('proveedores.index'));
    $this->assertSoftDeleted($proveedor);
});

test('cashiers can not access the suppliers module', function () {
    $cajero = Usuario::factory()->cajero()->create();

    $response = $this->actingAs($cajero)->get(route('proveedores.index'));

    $response->assertForbidden();
});
