<?php

use App\Models\Categoria;
use App\Models\Usuario;

test('administrators can view the categories list', function () {
    $admin = Usuario::factory()->administrador()->create();
    Categoria::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('categorias.index'));

    $response->assertOk();
});

test('administrators can create a category', function () {
    $admin = Usuario::factory()->administrador()->create();

    $response = $this->actingAs($admin)->post(route('categorias.store'), [
        'descripcion' => 'Lácteos',
    ]);

    $response->assertRedirect(route('categorias.index'));
    $this->assertDatabaseHas('categorias', ['descripcion' => 'Lácteos']);
});

test('category description must be unique', function () {
    $admin = Usuario::factory()->administrador()->create();
    Categoria::factory()->create(['descripcion' => 'Bebidas']);

    $response = $this->actingAs($admin)->post(route('categorias.store'), [
        'descripcion' => 'Bebidas',
    ]);

    $response->assertSessionHasErrors('descripcion');
});

test('administrators can update a category', function () {
    $admin = Usuario::factory()->administrador()->create();
    $categoria = Categoria::factory()->create();

    $response = $this->actingAs($admin)->put(route('categorias.update', $categoria), [
        'descripcion' => 'Actualizada',
    ]);

    $response->assertRedirect(route('categorias.index'));
    $this->assertDatabaseHas('categorias', ['id' => $categoria->id, 'descripcion' => 'Actualizada']);
});

test('administrators can delete a category', function () {
    $admin = Usuario::factory()->administrador()->create();
    $categoria = Categoria::factory()->create();

    $response = $this->actingAs($admin)->delete(route('categorias.destroy', $categoria));

    $response->assertRedirect(route('categorias.index'));
    $this->assertSoftDeleted($categoria);
});

test('cashiers can not access the categories module', function () {
    $cajero = Usuario::factory()->cajero()->create();

    $response = $this->actingAs($cajero)->get(route('categorias.index'));

    $response->assertForbidden();
});
