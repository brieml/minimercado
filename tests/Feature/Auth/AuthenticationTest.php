<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $usuario = Usuario::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post('/login', [
        'correo' => $usuario->correo,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($usuario);
    $response->assertRedirect(route('dashboard'));
});

test('users can not authenticate with invalid password', function () {
    $usuario = Usuario::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->post('/login', [
        'correo' => $usuario->correo,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('inactive users can not authenticate', function () {
    $usuario = Usuario::factory()->inactivo()->create([
        'password' => Hash::make('password'),
    ]);

    $this->post('/login', [
        'correo' => $usuario->correo,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $usuario = Usuario::factory()->create();

    $response = $this->actingAs($usuario)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/login');
});
