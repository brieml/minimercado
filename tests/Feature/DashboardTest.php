<?php

use App\Models\Usuario;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $usuario = Usuario::factory()->create();
    $this->actingAs($usuario);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
