<?php

test('la ruta raíz lleva al login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('al abrir la ruta raíz se termina viendo el formulario de login', function () {
    $this->withoutVite();

    $this->followingRedirects()
        ->get('/')
        ->assertOk()
        ->assertViewIs('auth.login')
        ->assertSee('Iniciar sesión');
});
