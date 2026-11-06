<?php

beforeEach(function () {
    $this->withoutVite();
});

test('el login se muestra con su formulario', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertViewIs('auth.login')
        ->assertSee('Iniciar sesión')
        ->assertSee(route('login.post'), false)
        ->assertSee('name="_token"', false);
});

test('el login no usa emojis como iconos', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    expect(preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', $html))->toBe(0);
});

test('los iconos del login son svg decorativos', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    $total = substr_count($html, '<svg');
    $ocultosAlLectorDePantalla = preg_match_all('/<svg[^>]*aria-hidden="true"/', $html);

    expect($total)->toBeGreaterThanOrEqual(6)
        ->and($ocultosAlLectorDePantalla)->toBe($total);
});

test('los campos del login tienen su icono dentro del contenedor del input', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    expect(preg_match_all('/<span class="input-icon">\s*<svg/', $html))->toBe(2);
});
