<?php

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Usuario;

beforeEach(function () {
    $this->withoutVite();
});

function datosValidosDeCliente(array $cambios = []): array
{
    return array_merge(['nombre' => 'Juan Pérez', 'direccion' => 'Barrio Centro', 'telefono' => '300 111 2233'], $cambios);
}

test('los invitados no pueden crear, actualizar ni eliminar clientes', function () {
    $cliente = Cliente::factory()->create(['nombre' => 'Ana Ruiz']);

    $this->post(route('clientes.store'), datosValidosDeCliente())->assertRedirect(route('login'));
    $this->put(route('clientes.update', $cliente), datosValidosDeCliente())->assertRedirect(route('login'));
    $this->delete(route('clientes.destroy', $cliente))->assertRedirect(route('login'));

    $this->assertDatabaseMissing('clientes', ['nombre' => 'Juan Pérez']);
    $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'nombre' => 'Ana Ruiz', 'deleted_at' => null]);
});

test('crea un cliente y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('clientes.store'), datosValidosDeCliente())
        ->assertRedirect(route('clientes.index'))
        ->assertSessionHas('success', 'Cliente creado correctamente.');

    $this->assertDatabaseHas('clientes', datosValidosDeCliente());
});

test('la dirección y el teléfono son opcionales', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('clientes.store'), ['nombre' => 'Cliente General', 'direccion' => '', 'telefono' => ''])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('clientes', ['nombre' => 'Cliente General', 'direccion' => null, 'telefono' => null]);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->followingRedirects()
        ->post(route('clientes.store'), datosValidosDeCliente())
        ->assertOk()
        ->assertViewIs('clientes.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Cliente creado correctamente.')
        ->assertSee('Juan Pérez');
});

test('rechaza datos inválidos al crear un cliente', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('clientes.store'), datosValidosDeCliente($cambios))->assertSessionHasErrors($campo);

    expect(Cliente::count())->toBe(0);
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'nombre de más de 100 caracteres' => [['nombre' => str_repeat('a', 101)], 'nombre'],
    'dirección de más de 200 caracteres' => [['direccion' => str_repeat('a', 201)], 'direccion'],
    'teléfono de más de 30 caracteres' => [['telefono' => str_repeat('3', 31)], 'telefono'],
    'teléfono con letras' => [['telefono' => '300abc'], 'telefono'],
]);

test('muestra los errores y conserva lo escrito cuando falla la validación al crear', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->from(route('clientes.create'))
        ->followingRedirects()
        ->post(route('clientes.store'), datosValidosDeCliente(['telefono' => 'no-es-telefono']))
        ->assertOk()
        ->assertViewIs('clientes.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El formato del campo teléfono no es válido.')
        ->assertSee('value="Juan Pérez"', false)
        ->assertSee('value="no-es-telefono"', false);
});

test('muestra el detalle de un cliente con sus facturas', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    Factura::factory()->create(['cliente_id' => $cliente->id, 'fecha' => '2026-11-03']);

    $this->get(route('clientes.show', $cliente))
        ->assertOk()
        ->assertViewIs('clientes.show')
        ->assertViewHas('cliente', fn ($vista) => $vista->is($cliente) && $vista->relationLoaded('facturas'))
        ->assertSee('Juan Pérez')
        ->assertSee('03/11/2026');
});

test('el listado ofrece el enlace al detalle de cada cliente', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create();

    $this->get(route('clientes.index'))->assertSee(route('clientes.show', $cliente), false);
});

test('actualiza un cliente y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Ana Ruiz']);

    $this->put(route('clientes.update', $cliente), datosValidosDeCliente(['nombre' => 'Ana María Ruiz']))
        ->assertRedirect(route('clientes.index'))
        ->assertSessionHas('success', 'Cliente actualizado correctamente.');

    expect($cliente->fresh()->nombre)->toBe('Ana María Ruiz');
});

test('rechaza datos inválidos al actualizar un cliente', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Ana Ruiz']);

    $this->put(route('clientes.update', $cliente), datosValidosDeCliente($cambios))->assertSessionHasErrors($campo);

    expect($cliente->fresh()->nombre)->toBe('Ana Ruiz');
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'teléfono con letras' => [['telefono' => '300abc'], 'telefono'],
]);

test('muestra los errores cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create();

    $this->from(route('clientes.edit', $cliente))
        ->followingRedirects()
        ->put(route('clientes.update', $cliente), datosValidosDeCliente(['nombre' => '']))
        ->assertOk()
        ->assertViewIs('clientes.edit')
        ->assertSee('El campo nombre es obligatorio.');
});

test('elimina un cliente (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create();

    $this->delete(route('clientes.destroy', $cliente))
        ->assertRedirect(route('clientes.index'))
        ->assertSessionHas('success', 'Cliente eliminado correctamente.');

    $this->assertSoftDeleted($cliente);
});

test('no elimina un cliente que tiene facturas y avisa con un mensaje de error', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create();
    Factura::factory()->create(['cliente_id' => $cliente->id]);

    $this->followingRedirects()
        ->delete(route('clientes.destroy', $cliente))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se puede eliminar el cliente porque tiene facturas relacionadas.');

    $this->assertNotSoftDeleted($cliente);
});

test('mostrar, actualizar o eliminar un cliente inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->get(route('clientes.show', 999999))->assertNotFound();
    $this->put(route('clientes.update', 999999), datosValidosDeCliente())->assertNotFound();
    $this->delete(route('clientes.destroy', 999999))->assertNotFound();
});
