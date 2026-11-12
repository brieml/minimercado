<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;

beforeEach(function () {
    $this->withoutVite();
});

function datosValidosDeProveedor(array $cambios = []): array
{
    return array_merge(['nombre' => 'Distribuciones Sucre', 'direccion' => 'Sincelejo', 'telefono' => '3012345678'], $cambios);
}

test('los invitados no pueden crear, actualizar ni eliminar proveedores', function () {
    $proveedor = Proveedor::factory()->create(['nombre' => 'Comercializadora Caribe']);

    $this->post(route('proveedores.store'), datosValidosDeProveedor())->assertRedirect(route('login'));
    $this->put(route('proveedores.update', $proveedor), datosValidosDeProveedor())->assertRedirect(route('login'));
    $this->delete(route('proveedores.destroy', $proveedor))->assertRedirect(route('login'));

    $this->assertDatabaseMissing('proveedores', ['nombre' => 'Distribuciones Sucre']);
    $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'nombre' => 'Comercializadora Caribe', 'deleted_at' => null]);
});

test('crea un proveedor y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('proveedores.store'), datosValidosDeProveedor())
        ->assertRedirect(route('proveedores.index'))
        ->assertSessionHas('success', 'Proveedor creado correctamente.');

    $this->assertDatabaseHas('proveedores', datosValidosDeProveedor());
});

test('la dirección y el teléfono son opcionales', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('proveedores.store'), ['nombre' => 'Proveedor local', 'direccion' => '', 'telefono' => ''])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('proveedores', ['nombre' => 'Proveedor local', 'direccion' => null, 'telefono' => null]);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->followingRedirects()
        ->post(route('proveedores.store'), datosValidosDeProveedor())
        ->assertOk()
        ->assertViewIs('proveedores.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Proveedor creado correctamente.')
        ->assertSee('Distribuciones Sucre');
});

test('rechaza datos inválidos al crear un proveedor', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('proveedores.store'), datosValidosDeProveedor($cambios))->assertSessionHasErrors($campo);

    expect(Proveedor::count())->toBe(0);
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'nombre de más de 120 caracteres' => [['nombre' => str_repeat('a', 121)], 'nombre'],
    'dirección de más de 200 caracteres' => [['direccion' => str_repeat('a', 201)], 'direccion'],
    'teléfono de más de 30 caracteres' => [['telefono' => str_repeat('3', 31)], 'telefono'],
    'teléfono con letras' => [['telefono' => '301abc'], 'telefono'],
]);

test('muestra los errores y conserva lo escrito cuando falla la validación al crear', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->from(route('proveedores.create'))
        ->followingRedirects()
        ->post(route('proveedores.store'), datosValidosDeProveedor(['telefono' => 'no-es-telefono']))
        ->assertOk()
        ->assertViewIs('proveedores.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El formato del campo teléfono no es válido.')
        ->assertSee('value="Distribuciones Sucre"', false)
        ->assertSee('value="no-es-telefono"', false);
});

test('muestra el detalle de un proveedor con sus productos', function () {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create(['nombre' => 'Distribuciones Sucre']);
    $categoria = Categoria::factory()->create(['descripcion' => 'Alimentos']);
    Producto::factory()->create(['proveedor_id' => $proveedor->id, 'categoria_id' => $categoria->id, 'descripcion' => 'Arroz 1 Kg']);

    $this->get(route('proveedores.show', $proveedor))
        ->assertOk()
        ->assertViewIs('proveedores.show')
        ->assertViewHas('proveedor', fn ($vista) => $vista->is($proveedor) && $vista->relationLoaded('productos'))
        ->assertSee('Distribuciones Sucre')
        ->assertSee('Arroz 1 Kg')
        ->assertSee('Alimentos');
});

test('el listado ofrece el enlace al detalle de cada proveedor', function () {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create();

    $this->get(route('proveedores.index'))->assertSee(route('proveedores.show', $proveedor), false);
});

test('actualiza un proveedor y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create(['nombre' => 'Comercializadora Caribe']);

    $this->put(route('proveedores.update', $proveedor), datosValidosDeProveedor(['nombre' => 'Caribe S.A.S.']))
        ->assertRedirect(route('proveedores.index'))
        ->assertSessionHas('success', 'Proveedor actualizado correctamente.');

    expect($proveedor->fresh()->nombre)->toBe('Caribe S.A.S.');
});

test('rechaza datos inválidos al actualizar un proveedor', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create(['nombre' => 'Comercializadora Caribe']);

    $this->put(route('proveedores.update', $proveedor), datosValidosDeProveedor($cambios))->assertSessionHasErrors($campo);

    expect($proveedor->fresh()->nombre)->toBe('Comercializadora Caribe');
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'teléfono con letras' => [['telefono' => '301abc'], 'telefono'],
]);

test('muestra los errores cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create();

    $this->from(route('proveedores.edit', $proveedor))
        ->followingRedirects()
        ->put(route('proveedores.update', $proveedor), datosValidosDeProveedor(['nombre' => '']))
        ->assertOk()
        ->assertViewIs('proveedores.edit')
        ->assertSee('El campo nombre es obligatorio.');
});

test('elimina un proveedor (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create();

    $this->delete(route('proveedores.destroy', $proveedor))
        ->assertRedirect(route('proveedores.index'))
        ->assertSessionHas('success', 'Proveedor eliminado correctamente.');

    $this->assertSoftDeleted($proveedor);
});

test('no elimina un proveedor que tiene productos y avisa con un mensaje de error', function () {
    $this->actingAs(Usuario::factory()->create());
    $proveedor = Proveedor::factory()->create();
    Producto::factory()->create(['proveedor_id' => $proveedor->id]);

    $this->followingRedirects()
        ->delete(route('proveedores.destroy', $proveedor))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se puede eliminar el proveedor porque tiene productos relacionados.');

    $this->assertNotSoftDeleted($proveedor);
});

test('mostrar, actualizar o eliminar un proveedor inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route('proveedores.show', 999999))->assertNotFound();
    $this->put(route('proveedores.update', 999999), datosValidosDeProveedor())->assertNotFound();
    $this->delete(route('proveedores.destroy', 999999))->assertNotFound();
});
