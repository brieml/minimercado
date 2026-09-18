<?php

use App\Models\Cliente;
use App\Models\Usuario;

test('cashiers can view the clients list', function () {
    $cajero = Usuario::factory()->cajero()->create();
    Cliente::factory()->count(3)->create();

    $response = $this->actingAs($cajero)->get(route('clientes.index'));

    $response->assertOk();
});

test('cashiers can create a client', function () {
    $cajero = Usuario::factory()->cajero()->create();

    $response = $this->actingAs($cajero)->post(route('clientes.store'), [
        'nombre' => 'Ana Torres',
        'direccion' => 'Barrio Nuevo',
        'telefono' => '3009998877',
    ]);

    $response->assertRedirect(route('clientes.index'));
    $this->assertDatabaseHas('clientes', ['nombre' => 'Ana Torres']);
});

test('client address and phone are optional', function () {
    $cajero = Usuario::factory()->cajero()->create();

    $response = $this->actingAs($cajero)->post(route('clientes.store'), [
        'nombre' => 'Cliente Sin Datos',
    ]);

    $response->assertRedirect(route('clientes.index'));
    $this->assertDatabaseHas('clientes', ['nombre' => 'Cliente Sin Datos', 'direccion' => null]);
});

test('cashiers can update a client', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $cliente = Cliente::factory()->create();

    $response = $this->actingAs($cajero)->put(route('clientes.update', $cliente), [
        'nombre' => 'Nombre Actualizado',
    ]);

    $response->assertRedirect(route('clientes.index'));
    $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'nombre' => 'Nombre Actualizado']);
});

test('cashiers can delete a client', function () {
    $cajero = Usuario::factory()->cajero()->create();
    $cliente = Cliente::factory()->create();

    $response = $this->actingAs($cajero)->delete(route('clientes.destroy', $cliente));

    $response->assertRedirect(route('clientes.index'));
    $this->assertSoftDeleted($cliente);
});

test('inventory users can not access the clients module', function () {
    $inventario = Usuario::factory()->inventario()->create();

    $response = $this->actingAs($inventario)->get(route('clientes.index'));

    $response->assertForbidden();
});
