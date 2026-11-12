<?php

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Venta;

beforeEach(function () {
    $this->withoutVite();
});

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

test('el dashboard entrega el total de registros de cada módulo al administrador', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    Producto::factory()->count(3)->create();
    Cliente::factory()->count(2)->create();
    Venta::factory()->create();
    Categoria::factory()->create()->delete();

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('dashboard')
        ->assertViewHas('totales', fn ($totales) => $totales->all() === [
            'productos' => 4,
            'categorias' => 4,
            'proveedores' => 4,
            'clientes' => 3,
            'facturas' => Factura::count(),
            'ventas' => 1,
            'usuarios' => Usuario::count(),
        ]);
});

test('el dashboard muestra el nombre y el rol del usuario con sesión iniciada', function () {
    $usuario = Usuario::factory()->cajero()->create();

    $this->actingAs($usuario)
        ->get(route('dashboard'))
        ->assertSee($usuario->nombre_completo)
        ->assertSee($usuario->correo)
        ->assertSee('Cajero');
});
