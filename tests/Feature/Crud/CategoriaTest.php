<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Usuario;

beforeEach(function () {
    $this->withoutVite();
});

test('los invitados no pueden crear, actualizar ni eliminar categorías', function () {
    $categoria = Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->post(route('categorias.store'), ['descripcion' => 'Lácteos'])->assertRedirect(route('login'));
    $this->put(route('categorias.update', $categoria), ['descripcion' => 'Lácteos'])->assertRedirect(route('login'));
    $this->delete(route('categorias.destroy', $categoria))->assertRedirect(route('login'));

    $this->assertDatabaseMissing('categorias', ['descripcion' => 'Lácteos']);
    $this->assertDatabaseHas('categorias', ['id' => $categoria->id, 'descripcion' => 'Bebidas', 'deleted_at' => null]);
});

test('crea una categoría y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('categorias.store'), ['descripcion' => 'Lácteos'])
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('success', 'Categoría creada correctamente.');

    $this->assertDatabaseHas('categorias', ['descripcion' => 'Lácteos']);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->followingRedirects()
        ->post(route('categorias.store'), ['descripcion' => 'Lácteos'])
        ->assertOk()
        ->assertViewIs('categorias.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Categoría creada correctamente.')
        ->assertSee('Lácteos');
});

test('rechaza datos inválidos al crear una categoría', function (array $datos) {
    $this->actingAs(Usuario::factory()->create());
    Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->post(route('categorias.store'), $datos)->assertSessionHasErrors('descripcion');

    expect(Categoria::count())->toBe(1);
})->with([
    'sin descripción' => [['descripcion' => '']],
    'descripción de más de 100 caracteres' => [['descripcion' => str_repeat('a', 101)]],
    'descripción repetida' => [['descripcion' => 'Bebidas']],
]);

test('muestra los errores y conserva lo escrito cuando falla la validación al crear', function () {
    $this->actingAs(Usuario::factory()->create());
    Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->from(route('categorias.create'))
        ->followingRedirects()
        ->post(route('categorias.store'), ['descripcion' => 'Bebidas'])
        ->assertOk()
        ->assertViewIs('categorias.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El valor del campo descripción ya está en uso.')
        ->assertSee('value="Bebidas"', false);
});

test('muestra el detalle de una categoría con sus productos', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create(['descripcion' => 'Bebidas']);
    Producto::factory()->create(['categoria_id' => $categoria->id, 'descripcion' => 'Gaseosa 1.5 Litros']);

    $this->get(route('categorias.show', $categoria))
        ->assertOk()
        ->assertViewIs('categorias.show')
        ->assertViewHas('categoria', fn ($vista) => $vista->is($categoria) && $vista->relationLoaded('productos'))
        ->assertSee('Bebidas')
        ->assertSee('Gaseosa 1.5 Litros');
});

test('el listado ofrece el enlace al detalle de cada categoría', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create();

    $this->get(route('categorias.index'))->assertSee(route('categorias.show', $categoria), false);
});

test('actualiza una categoría y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->put(route('categorias.update', $categoria), ['descripcion' => 'Gaseosas'])
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('success', 'Categoría actualizada correctamente.');

    expect($categoria->fresh()->descripcion)->toBe('Gaseosas');
});

test('permite guardar una categoría con su misma descripción', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->put(route('categorias.update', $categoria), ['descripcion' => 'Bebidas'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('categorias.index'));
});

test('rechaza datos inválidos al actualizar una categoría', function (array $datos) {
    $this->actingAs(Usuario::factory()->create());
    Categoria::factory()->create(['descripcion' => 'Aseo']);
    $categoria = Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->put(route('categorias.update', $categoria), $datos)->assertSessionHasErrors('descripcion');

    expect($categoria->fresh()->descripcion)->toBe('Bebidas');
})->with([
    'sin descripción' => [['descripcion' => '']],
    'descripción de más de 100 caracteres' => [['descripcion' => str_repeat('a', 101)]],
    'descripción de otra categoría' => [['descripcion' => 'Aseo']],
]);

test('muestra los errores cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $this->from(route('categorias.edit', $categoria))
        ->followingRedirects()
        ->put(route('categorias.update', $categoria), ['descripcion' => ''])
        ->assertOk()
        ->assertViewIs('categorias.edit')
        ->assertSee('El campo descripción es obligatorio.');
});

test('elimina una categoría (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create();

    $this->delete(route('categorias.destroy', $categoria))
        ->assertRedirect(route('categorias.index'))
        ->assertSessionHas('success', 'Categoría eliminada correctamente.');

    $this->assertSoftDeleted($categoria);
});

test('no elimina una categoría que tiene productos y avisa con un mensaje de error', function () {
    $this->actingAs(Usuario::factory()->create());
    $categoria = Categoria::factory()->create();
    Producto::factory()->create(['categoria_id' => $categoria->id]);

    $this->followingRedirects()
        ->delete(route('categorias.destroy', $categoria))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se puede eliminar la categoría porque tiene productos relacionados.');

    $this->assertNotSoftDeleted($categoria);
});

test('mostrar, editar, actualizar o eliminar una categoría inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route('categorias.show', 999999))->assertNotFound();
    $this->put(route('categorias.update', 999999), ['descripcion' => 'Lácteos'])->assertNotFound();
    $this->delete(route('categorias.destroy', 999999))->assertNotFound();
});
