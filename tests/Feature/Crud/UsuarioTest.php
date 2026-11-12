<?php

use App\Models\Factura;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosValidosDeUsuario(array $cambios = []): array
{
    return array_merge([
        'nombre' => 'Laura',
        'apellido' => 'Gómez',
        'correo' => 'laura.gomez@minimercado.test',
        'password' => 'secreto123',
        'rol' => 'Cajero',
        'estado' => 'Activo',
    ], $cambios);
}

test('los invitados no pueden crear, actualizar ni eliminar usuarios', function () {
    $usuario = Usuario::factory()->create(['nombre' => 'Marta']);

    $this->post(route('usuarios.store'), datosValidosDeUsuario())->assertRedirect(route('login'));
    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario())->assertRedirect(route('login'));
    $this->delete(route('usuarios.destroy', $usuario))->assertRedirect(route('login'));

    $this->assertDatabaseMissing('usuarios', ['correo' => 'laura.gomez@minimercado.test']);
    $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'nombre' => 'Marta', 'deleted_at' => null]);
});

test('crea un usuario y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('usuarios.store'), datosValidosDeUsuario())
        ->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('success', 'Usuario creado correctamente.');

    $this->assertDatabaseHas('usuarios', [
        'nombre' => 'Laura',
        'apellido' => 'Gómez',
        'correo' => 'laura.gomez@minimercado.test',
        'rol' => 'Cajero',
        'estado' => 'Activo',
    ]);
});

test('guarda la contraseña cifrada y el usuario creado puede iniciar sesión con ella', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('usuarios.store'), datosValidosDeUsuario())->assertSessionHasNoErrors();

    $nuevo = Usuario::where('correo', 'laura.gomez@minimercado.test')->firstOrFail();

    expect($nuevo->password)->not->toBe('secreto123')
        ->and(Hash::check('secreto123', $nuevo->password))->toBeTrue();

    $this->post(route('logout'));
    $this->post(route('login.post'), ['correo' => 'laura.gomez@minimercado.test', 'password' => 'secreto123'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($nuevo);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->followingRedirects()
        ->post(route('usuarios.store'), datosValidosDeUsuario())
        ->assertOk()
        ->assertViewIs('usuarios.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Usuario creado correctamente.')
        ->assertSee('Laura Gómez');
});

test('rechaza datos inválidos al crear un usuario', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->create());

    $this->post(route('usuarios.store'), datosValidosDeUsuario($cambios))->assertSessionHasErrors($campo);

    expect(Usuario::count())->toBe(1);
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'nombre de más de 100 caracteres' => [['nombre' => str_repeat('a', 101)], 'nombre'],
    'sin apellido' => [['apellido' => ''], 'apellido'],
    'apellido de más de 100 caracteres' => [['apellido' => str_repeat('a', 101)], 'apellido'],
    'sin correo' => [['correo' => ''], 'correo'],
    'correo con formato inválido' => [['correo' => 'no-es-un-correo'], 'correo'],
    'correo de más de 150 caracteres' => [['correo' => str_repeat('a', 145).'@x.com'], 'correo'],
    'sin contraseña' => [['password' => ''], 'password'],
    'contraseña de menos de 8 caracteres' => [['password' => '1234567'], 'password'],
    'sin rol' => [['rol' => ''], 'rol'],
    'rol que no existe' => [['rol' => 'Superadministrador'], 'rol'],
    'sin estado' => [['estado' => ''], 'estado'],
    'estado que no existe' => [['estado' => 'Bloqueado'], 'estado'],
]);

test('no permite repetir el correo de otro usuario', function () {
    $this->actingAs(Usuario::factory()->create());
    Usuario::factory()->create(['correo' => 'laura.gomez@minimercado.test']);

    $this->from(route('usuarios.create'))
        ->followingRedirects()
        ->post(route('usuarios.store'), datosValidosDeUsuario())
        ->assertOk()
        ->assertSee('El valor del campo correo electrónico ya está en uso.');

    expect(Usuario::where('correo', 'laura.gomez@minimercado.test')->count())->toBe(1);
});

test('tampoco permite reutilizar el correo de un usuario eliminado', function () {
    $this->actingAs(Usuario::factory()->create());
    Usuario::factory()->create(['correo' => 'laura.gomez@minimercado.test'])->delete();

    $this->post(route('usuarios.store'), datosValidosDeUsuario())->assertSessionHasErrors('correo');
});

test('muestra los errores, conserva lo escrito y nunca devuelve la contraseña al fallar al crear', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->from(route('usuarios.create'))
        ->followingRedirects()
        ->post(route('usuarios.store'), datosValidosDeUsuario(['correo' => 'no-es-un-correo', 'rol' => 'Inventario']))
        ->assertOk()
        ->assertViewIs('usuarios.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El campo correo electrónico debe ser una dirección de correo electrónico válida.')
        ->assertSee('value="Laura"', false)
        ->assertSee('value="Gómez"', false)
        ->assertSee('value="no-es-un-correo"', false)
        ->assertSee('<option value="Inventario" selected>', false)
        ->assertDontSee('secreto123');
});

test('muestra el detalle de un usuario con sus facturas y sin exponer la contraseña', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create(['nombre' => 'Laura', 'apellido' => 'Gómez', 'rol' => 'Cajero']);
    Factura::factory()->create(['usuario_id' => $usuario->id, 'fecha' => '2026-11-03']);

    $this->get(route('usuarios.show', $usuario))
        ->assertOk()
        ->assertViewIs('usuarios.show')
        ->assertViewHas('usuario', fn ($vista) => $vista->is($usuario) && $vista->relationLoaded('facturas'))
        ->assertSee('Laura Gómez')
        ->assertSee($usuario->correo)
        ->assertSee('Cajero')
        ->assertSee('03/11/2026')
        ->assertDontSee($usuario->password, false);
});

test('el listado ofrece el enlace al detalle de cada usuario', function () {
    $this->actingAs($actual = Usuario::factory()->create());

    $this->get(route('usuarios.index'))->assertSee(route('usuarios.show', $actual), false);
});

test('actualiza un usuario y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create(['nombre' => 'Marta', 'rol' => 'Cajero']);

    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario(['nombre' => 'Marta Elena', 'rol' => 'Inventario']))
        ->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('success', 'Usuario actualizado correctamente.');

    $usuario->refresh();

    expect($usuario->nombre)->toBe('Marta Elena')
        ->and($usuario->rol)->toBe('Inventario')
        ->and($usuario->correo)->toBe('laura.gomez@minimercado.test');
});

test('conserva la contraseña actual cuando se deja en blanco al actualizar', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create(['password' => Hash::make('clave-anterior')]);

    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario(['password' => '']))
        ->assertSessionHasNoErrors();

    expect(Hash::check('clave-anterior', $usuario->fresh()->password))->toBeTrue();
});

test('cambia la contraseña, cifrada, cuando se escribe una nueva al actualizar', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create(['password' => Hash::make('clave-anterior')]);

    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario(['password' => 'clave-nueva-123']))
        ->assertSessionHasNoErrors();

    $guardada = $usuario->fresh()->password;

    expect($guardada)->not->toBe('clave-nueva-123')
        ->and(Hash::check('clave-nueva-123', $guardada))->toBeTrue()
        ->and(Hash::check('clave-anterior', $guardada))->toBeFalse();
});

test('puede conservar su propio correo al actualizar, pero no tomar el de otro usuario', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create(['correo' => 'marta@minimercado.test']);
    Usuario::factory()->create(['correo' => 'otro@minimercado.test']);

    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario(['correo' => 'marta@minimercado.test']))
        ->assertSessionHasNoErrors();

    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario(['correo' => 'otro@minimercado.test']))
        ->assertSessionHasErrors('correo');

    expect($usuario->fresh()->correo)->toBe('marta@minimercado.test');
});

test('rechaza datos inválidos al actualizar un usuario', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create(['nombre' => 'Marta']);

    $this->put(route('usuarios.update', $usuario), datosValidosDeUsuario($cambios))->assertSessionHasErrors($campo);

    expect($usuario->fresh()->nombre)->toBe('Marta');
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'correo con formato inválido' => [['correo' => 'no-es-un-correo'], 'correo'],
    'contraseña de menos de 8 caracteres' => [['password' => '1234567'], 'password'],
    'rol que no existe' => [['rol' => 'Superadministrador'], 'rol'],
    'estado que no existe' => [['estado' => 'Bloqueado'], 'estado'],
]);

test('muestra los errores al actualizar sin devolver nunca la contraseña en el formulario', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create();

    $this->from(route('usuarios.edit', $usuario))
        ->followingRedirects()
        ->put(route('usuarios.update', $usuario), datosValidosDeUsuario(['nombre' => '', 'password' => 'corta']))
        ->assertOk()
        ->assertViewIs('usuarios.edit')
        ->assertSee('El campo nombre es obligatorio.')
        ->assertSee('El campo contraseña debe tener al menos 8 caracteres.')
        ->assertDontSee('value="corta"', false);
});

test('un usuario no puede desactivarse a sí mismo, pero sí a otro', function () {
    $this->actingAs($actual = Usuario::factory()->create());
    $otro = Usuario::factory()->create();

    $this->from(route('usuarios.edit', $actual))
        ->followingRedirects()
        ->put(route('usuarios.update', $actual), datosValidosDeUsuario(['estado' => 'Inactivo']))
        ->assertSee('No puedes desactivar tu propio usuario.');

    expect($actual->fresh()->estado)->toBe('Activo');

    $this->put(route('usuarios.update', $otro), datosValidosDeUsuario(['estado' => 'Inactivo']))
        ->assertSessionHasNoErrors();

    expect($otro->fresh()->estado)->toBe('Inactivo');
});

test('elimina otro usuario (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create();

    $this->delete(route('usuarios.destroy', $usuario))
        ->assertRedirect(route('usuarios.index'))
        ->assertSessionHas('success', 'Usuario eliminado correctamente.');

    $this->assertSoftDeleted($usuario);
});

test('no permite que un usuario elimine su propia cuenta', function () {
    $this->actingAs($actual = Usuario::factory()->create());

    $this->followingRedirects()
        ->delete(route('usuarios.destroy', $actual))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No puedes eliminar tu propio usuario.');

    $this->assertNotSoftDeleted($actual);
});

test('no elimina un usuario que tiene facturas y avisa con un mensaje de error', function () {
    $this->actingAs(Usuario::factory()->create());
    $usuario = Usuario::factory()->create();
    Factura::factory()->create(['usuario_id' => $usuario->id]);

    $this->followingRedirects()
        ->delete(route('usuarios.destroy', $usuario))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se puede eliminar el usuario porque tiene facturas relacionadas.');

    $this->assertNotSoftDeleted($usuario);
});

test('mostrar, actualizar o eliminar un usuario inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->create());

    $this->get(route('usuarios.show', 999999))->assertNotFound();
    $this->put(route('usuarios.update', 999999), datosValidosDeUsuario())->assertNotFound();
    $this->delete(route('usuarios.destroy', 999999))->assertNotFound();
});
