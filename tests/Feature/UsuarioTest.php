<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

test('administrators can view the users list', function () {
    $admin = Usuario::factory()->administrador()->create();

    $response = $this->actingAs($admin)->get(route('usuarios.index'));

    $response->assertOk();
});

test('administrators can create a user with a hashed password', function () {
    $admin = Usuario::factory()->administrador()->create();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'nombre' => 'Nuevo',
        'apellido' => 'Usuario',
        'correo' => 'nuevo@minimercado.com',
        'password' => 'clave12345',
        'rol' => 'Cajero',
        'estado' => 'Activo',
    ]);

    $response->assertRedirect(route('usuarios.index'));

    $usuario = Usuario::whereCorreo('nuevo@minimercado.com')->first();
    expect($usuario)->not->toBeNull();
    expect(Hash::check('clave12345', $usuario->password))->toBeTrue();
});

test('user email must be unique', function () {
    $admin = Usuario::factory()->administrador()->create();
    $existente = Usuario::factory()->create();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'nombre' => 'Duplicado',
        'apellido' => 'Correo',
        'correo' => $existente->correo,
        'password' => 'clave12345',
        'rol' => 'Cajero',
        'estado' => 'Activo',
    ]);

    $response->assertSessionHasErrors('correo');
});

test('updating a user without a password keeps the current one', function () {
    $admin = Usuario::factory()->administrador()->create();
    $usuario = Usuario::factory()->create();
    $passwordOriginal = $usuario->password;

    $response = $this->actingAs($admin)->put(route('usuarios.update', $usuario), [
        'nombre' => $usuario->nombre,
        'apellido' => $usuario->apellido,
        'correo' => $usuario->correo,
        'rol' => $usuario->rol,
        'estado' => $usuario->estado,
    ]);

    $response->assertRedirect(route('usuarios.index'));
    expect($usuario->fresh()->password)->toBe($passwordOriginal);
});

test('administrators can not deactivate their own account', function () {
    $admin = Usuario::factory()->administrador()->create();

    $response = $this->actingAs($admin)->put(route('usuarios.update', $admin), [
        'nombre' => $admin->nombre,
        'apellido' => $admin->apellido,
        'correo' => $admin->correo,
        'rol' => $admin->rol,
        'estado' => 'Inactivo',
    ]);

    $response->assertForbidden();
    expect($admin->fresh()->estado)->toBe('Activo');
});

test('administrators can not delete their own account', function () {
    $admin = Usuario::factory()->administrador()->create();

    $response = $this->actingAs($admin)->delete(route('usuarios.destroy', $admin));

    $response->assertForbidden();
    $this->assertDatabaseHas('usuarios', ['id' => $admin->id, 'deleted_at' => null]);
});

test('administrators can delete other users', function () {
    $admin = Usuario::factory()->administrador()->create();
    $usuario = Usuario::factory()->create();

    $response = $this->actingAs($admin)->delete(route('usuarios.destroy', $usuario));

    $response->assertRedirect(route('usuarios.index'));
    $this->assertSoftDeleted($usuario);
});

test('cashiers can not access the users module', function () {
    $cajero = Usuario::factory()->cajero()->create();

    $response = $this->actingAs($cajero)->get(route('usuarios.index'));

    $response->assertForbidden();
});
