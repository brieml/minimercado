<?php

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Venta;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Datos válidos para registrar una venta: un cliente y una línea de un producto por unidades.
 * El cliente y el producto solo se crean cuando la prueba no los sobrescribe, y el texto
 * "{producto}" dentro de $cambios se reemplaza por el id de un producto por unidades.
 *
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosValidosDeVenta(array $cambios = []): array
{
    $datos = array_merge([
        'cliente_id' => $cambios['cliente_id'] ?? Cliente::factory()->create()->id,
        'lineas' => $cambios['lineas'] ?? [['producto_id' => '{producto}', 'cantidad' => '2']],
    ], $cambios);

    if (str_contains(json_encode($datos), '{producto}')) {
        $producto = Producto::factory()->create();

        array_walk_recursive($datos, function (&$valor) use ($producto) {
            $valor = $valor === '{producto}' ? $producto->id : $valor;
        });
    }

    return $datos;
}

/**
 * Datos válidos para corregir una sola venta (producto por unidades y cantidad entera).
 *
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosValidosParaEditarVenta(array $cambios = []): array
{
    return array_merge([
        'producto_id' => $cambios['producto_id'] ?? Producto::factory()->create()->id,
        'cantidad' => '2',
    ], $cambios);
}

/**
 * Indica si, en el select de una línea, está elegida la opción del producto.
 */
function productoElegidoEnLinea(string $html, int $indice, int $productoId): bool
{
    return preg_match('/name="lineas\['.$indice.'\]\[producto_id\]".*?<option value="'.$productoId.'"[^>]*\sselected[\s>]/s', $html) === 1;
}

/**
 * Valor escrito en un campo (por su atributo name), o null si el campo no tiene valor.
 */
function valorDelCampo(string $html, string $nombre): ?string
{
    return preg_match('/name="'.preg_quote($nombre, '/').'"[^>]*\svalue="([^"]*)"/s', $html, $coincidencia) === 1
        ? $coincidencia[1]
        : null;
}

test('los invitados no pueden registrar, corregir ni eliminar ventas', function () {
    $venta = Venta::factory()->create(['cantidad' => 4]);

    $this->post(route('ventas.store'), datosValidosDeVenta())->assertRedirect(route('login'));
    $this->put(route('ventas.update', $venta), datosValidosParaEditarVenta())->assertRedirect(route('login'));
    $this->delete(route('ventas.destroy', $venta))->assertRedirect(route('login'));

    expect(Venta::count())->toBe(1)
        ->and(Factura::count())->toBe(1)
        ->and((float) $venta->fresh()->cantidad)->toBe(4.0)
        ->and($venta->fresh()->trashed())->toBeFalse();
});

/*
 * Registro de la venta: crea la factura automáticamente.
 */

test('al registrar una venta se crea la factura del cliente con la fecha de hoy y el usuario que atiende', function () {
    $this->actingAs($cajero = Usuario::factory()->cajero()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $leche = Producto::factory()->create();
    $arroz = Producto::factory()->conGramaje()->create();

    $this->post(route('ventas.store'), [
        'cliente_id' => $cliente->id,
        'lineas' => [
            ['producto_id' => $leche->id, 'cantidad' => '2'],
            ['producto_id' => $arroz->id, 'cantidad' => '1.250'],
        ],
    ])
        ->assertRedirect(route('facturas.show', Factura::firstOrFail()))
        ->assertSessionHas('success', 'Venta registrada. Se generó la factura #'.Factura::firstOrFail()->id.'.');

    $factura = Factura::with('ventas')->firstOrFail();

    expect(Factura::count())->toBe(1)
        ->and($factura->fecha->toDateString())->toBe(now()->toDateString())
        ->and($factura->cliente_id)->toBe($cliente->id)
        ->and($factura->usuario_id)->toBe($cajero->id)
        ->and($factura->ventas)->toHaveCount(2)
        ->and($factura->ventas->pluck('producto_id')->all())->toBe([$leche->id, $arroz->id])
        ->and($factura->ventas->map(fn (Venta $venta) => (float) $venta->cantidad)->all())->toBe([2.0, 1.25]);
});

test('el usuario y la fecha de la factura no se pueden escoger desde la petición', function () {
    $this->actingAs($cajero = Usuario::factory()->cajero()->create());
    $otroUsuario = Usuario::factory()->create();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'usuario_id' => $otroUsuario->id,
        'fecha' => '2020-01-01',
        'factura_id' => 999,
    ]))->assertSessionHasNoErrors();

    $factura = Factura::firstOrFail();

    expect($factura->usuario_id)->toBe($cajero->id)
        ->and($factura->fecha->toDateString())->toBe(now()->toDateString())
        ->and(Venta::firstOrFail()->factura_id)->toBe($factura->id);
});

test('después de registrar la venta se muestra la factura generada con el cliente, los productos y el total', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $leche = Producto::factory()->create(['descripcion' => 'Leche entera 1 L', 'precio' => 1000]);
    $arroz = Producto::factory()->conGramaje()->create(['descripcion' => 'Arroz Diana', 'precio' => 4500]);

    $this->followingRedirects()
        ->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'lineas' => [
                ['producto_id' => $leche->id, 'cantidad' => '2'],
                ['producto_id' => $arroz->id, 'cantidad' => '1.25'],
            ],
        ])
        ->assertOk()
        ->assertViewIs('facturas.show')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Venta registrada. Se generó la factura #'.Factura::firstOrFail()->id.'.')
        ->assertSee('Juan Pérez')
        ->assertSee('Leche entera 1 L')
        ->assertSee('Arroz Diana')
        ->assertSee('$ 2.000,00', false)
        ->assertSee('$ 5.625,00', false)
        ->assertSee('$ 7.625,00', false);
});

test('cada venta genera su propia factura', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());

    $this->post(route('ventas.store'), datosValidosDeVenta());
    $this->post(route('ventas.store'), datosValidosDeVenta());

    expect(Factura::count())->toBe(2)
        ->and(Venta::count())->toBe(2)
        ->and(Venta::pluck('factura_id')->unique())->toHaveCount(2);
});

test('si falla al guardar una línea no queda ninguna factura ni venta', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $cliente = Cliente::factory()->create();
    $primero = Producto::factory()->create();
    $segundo = Producto::factory()->create();

    $llamadas = 0;
    Venta::creating(function () use (&$llamadas) {
        if (++$llamadas === 2) {
            throw new RuntimeException('falla simulada');
        }
    });

    $this->from(route('ventas.create'))
        ->followingRedirects()
        ->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'lineas' => [
                ['producto_id' => $primero->id, 'cantidad' => '1'],
                ['producto_id' => $segundo->id, 'cantidad' => '3'],
            ],
        ])
        ->assertOk()
        ->assertViewIs('ventas.create')
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se pudo registrar la venta. Intenta de nuevo.');

    expect(Factura::count())->toBe(0)
        ->and(Venta::count())->toBe(0);
});

test('la venta que falla conserva lo escrito en el formulario', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $cliente = Cliente::factory()->create();
    $producto = Producto::factory()->create();

    Venta::creating(fn () => throw new RuntimeException('falla simulada'));

    $html = $this->from(route('ventas.create'))
        ->followingRedirects()
        ->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'lineas' => [['producto_id' => $producto->id, 'cantidad' => '7']],
        ])
        ->getContent();

    expect(productoElegidoEnLinea($html, 0, $producto->id))->toBeTrue()
        ->and(valorDelCampo($html, 'lineas[0][cantidad]'))->toBe('7')
        ->and($html)->toMatch('/<option value="'.$cliente->id.'" selected>/');
});

/*
 * Cantidades: solo los productos con gramaje admiten decimales.
 */

test('un producto con gramaje admite cantidades enteras y decimales de hasta tres cifras', function (string $cantidad) {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $producto = Producto::factory()->conGramaje()->create();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [['producto_id' => $producto->id, 'cantidad' => $cantidad]],
    ]))->assertSessionHasNoErrors();

    expect(Venta::count())->toBe(1)
        ->and((float) Venta::first()->cantidad)->toBe((float) $cantidad);
})->with(['1', '0.001', '0.25', '1.5', '10.250', '9999999.999']);

test('un producto sin gramaje solo admite cantidades enteras', function (string $cantidad) {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $producto = Producto::factory()->create();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [['producto_id' => $producto->id, 'cantidad' => $cantidad]],
    ]))->assertSessionHasNoErrors();

    expect(Venta::count())->toBe(1)
        ->and((float) Venta::first()->cantidad)->toBe((float) $cantidad);
})->with(['1', '2', '15', '3.000']);

test('un producto sin gramaje rechaza los decimales con un mensaje claro', function (string $cantidad) {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $producto = Producto::factory()->create(['descripcion' => 'Gaseosa 1.5 Litros']);

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [['producto_id' => $producto->id, 'cantidad' => $cantidad]],
    ]))->assertSessionHasErrors([
        'lineas.0.cantidad' => '«Gaseosa 1.5 Litros» no se vende por gramaje: la cantidad debe ser un número entero.',
    ]);

    expect(Venta::count())->toBe(0)
        ->and(Factura::count())->toBe(0);
})->with(['1.5', '0.5', '2.001', '.5']);

test('un producto con gramaje rechaza más de tres decimales con un mensaje claro', function (string $cantidad) {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $producto = Producto::factory()->conGramaje()->create(['descripcion' => 'Arroz Diana']);

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [['producto_id' => $producto->id, 'cantidad' => $cantidad]],
    ]))->assertSessionHasErrors([
        'lineas.0.cantidad' => 'La cantidad de «Arroz Diana» debe ser un número con hasta 3 decimales.',
    ]);

    expect(Venta::count())->toBe(0);
})->with(['1.2345', '0.0005', '2.0001']);

test('en una misma venta los decimales se aceptan o rechazan según el producto de cada línea', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $porGramaje = Producto::factory()->conGramaje()->create();
    $porUnidad = Producto::factory()->create();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [
            ['producto_id' => $porGramaje->id, 'cantidad' => '1.5'],
            ['producto_id' => $porUnidad->id, 'cantidad' => '1.5'],
        ],
    ]))
        ->assertSessionHasErrors('lineas.1.cantidad')
        ->assertSessionDoesntHaveErrors('lineas.0.cantidad');

    expect(Factura::count())->toBe(0);
});

test('rechaza una cantidad inválida sin importar el producto', function (string $cantidad, string $tipo) {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $producto = $tipo === 'gramaje' ? Producto::factory()->conGramaje()->create() : Producto::factory()->create();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [['producto_id' => $producto->id, 'cantidad' => $cantidad]],
    ]))->assertSessionHasErrors('lineas.0.cantidad');

    expect(Venta::count())->toBe(0);
})->with([
    'vacía por unidades' => ['', 'unidad'],
    'vacía por gramaje' => ['', 'gramaje'],
    'texto por unidades' => ['abc', 'unidad'],
    'texto por gramaje' => ['abc', 'gramaje'],
    'cero por unidades' => ['0', 'unidad'],
    'cero por gramaje' => ['0', 'gramaje'],
    'negativa por unidades' => ['-1', 'unidad'],
    'negativa por gramaje' => ['-0.5', 'gramaje'],
    'por encima del máximo por unidades' => ['10000000', 'unidad'],
    'por encima del máximo por gramaje' => ['10000000', 'gramaje'],
    'notación científica por gramaje' => ['1e3', 'gramaje'],
]);

/*
 * Validación de la estructura de la venta.
 */

test('rechaza datos inválidos al registrar una venta', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->cajero()->create());

    $this->post(route('ventas.store'), datosValidosDeVenta($cambios))->assertSessionHasErrors($campo);

    expect(Venta::count())->toBe(0)
        ->and(Factura::count())->toBe(0);
})->with([
    'sin cliente' => [['cliente_id' => ''], 'cliente_id'],
    'cliente inexistente' => [['cliente_id' => 999999], 'cliente_id'],
    'sin ninguna línea' => [['lineas' => []], 'lineas'],
    'líneas que no son una lista' => [['lineas' => 'texto'], 'lineas'],
    'línea sin producto' => [['lineas' => [['producto_id' => '', 'cantidad' => '1']]], 'lineas.0.producto_id'],
    'producto inexistente' => [['lineas' => [['producto_id' => 999999, 'cantidad' => '1']]], 'lineas.0.producto_id'],
    'producto enviado como lista' => [['lineas' => [['producto_id' => [1, 2], 'cantidad' => '1']]], 'lineas.0.producto_id'],
    'línea sin cantidad' => [['lineas' => [['producto_id' => '{producto}', 'cantidad' => '']]], 'lineas.0.cantidad'],
]);

test('si el producto no existe o está eliminado solo se informa el producto y no la cantidad', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $eliminado = Producto::factory()->create();
    $eliminado->delete();

    foreach ([999999, $eliminado->id] as $productoId) {
        $this->post(route('ventas.store'), datosValidosDeVenta([
            'lineas' => [['producto_id' => $productoId, 'cantidad' => '1.5']],
        ]))
            ->assertSessionHasErrors('lineas.0.producto_id')
            ->assertSessionDoesntHaveErrors('lineas.0.cantidad');
    }
});

test('rechaza una venta sin el campo de líneas con un mensaje claro', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());

    $this->post(route('ventas.store'), ['cliente_id' => Cliente::factory()->create()->id])
        ->assertSessionHasErrors(['lineas' => 'Agrega al menos un producto a la venta.']);
});

test('una venta admite como máximo cincuenta productos', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $lineas = Producto::factory()->count(51)->create()
        ->map(fn (Producto $producto) => ['producto_id' => $producto->id, 'cantidad' => '1'])
        ->all();

    $this->post(route('ventas.store'), datosValidosDeVenta(['lineas' => $lineas]))
        ->assertSessionHasErrors(['lineas' => 'Una venta admite como máximo 50 productos.']);

    expect(Venta::count())->toBe(0);
});

test('un producto no puede repetirse en la misma venta', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $producto = Producto::factory()->create();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'lineas' => [
            ['producto_id' => $producto->id, 'cantidad' => '1'],
            ['producto_id' => $producto->id, 'cantidad' => '2'],
        ],
    ]))->assertSessionHasErrors([
        'lineas.1.producto_id' => 'Un producto solo puede aparecer una vez en la venta: suma las cantidades en una sola línea.',
    ]);

    expect(Venta::count())->toBe(0)
        ->and(Factura::count())->toBe(0);
});

test('no acepta un cliente ni un producto que ya fueron eliminados', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $clienteEliminado = Cliente::factory()->create();
    $productoEliminado = Producto::factory()->create();
    $clienteEliminado->delete();
    $productoEliminado->delete();

    $this->post(route('ventas.store'), datosValidosDeVenta([
        'cliente_id' => $clienteEliminado->id,
        'lineas' => [['producto_id' => $productoEliminado->id, 'cantidad' => '1']],
    ]))->assertSessionHasErrors(['cliente_id', 'lineas.0.producto_id']);

    expect(Venta::count())->toBe(0)
        ->and(Factura::count())->toBe(0);
});

test('muestra los errores y conserva el cliente y las líneas cuando falla la validación', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $cliente = Cliente::factory()->create();
    $porUnidad = Producto::factory()->create(['descripcion' => 'Gaseosa']);
    $porGramaje = Producto::factory()->conGramaje()->create();

    $html = $this->from(route('ventas.create'))
        ->followingRedirects()
        ->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'lineas' => [
                ['producto_id' => $porGramaje->id, 'cantidad' => '0.750'],
                ['producto_id' => $porUnidad->id, 'cantidad' => '2.5'],
            ],
        ])
        ->assertOk()
        ->assertViewIs('ventas.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('«Gaseosa» no se vende por gramaje: la cantidad debe ser un número entero.')
        ->getContent();

    expect($html)->toMatch('/<option value="'.$cliente->id.'" selected>/')
        ->and(productoElegidoEnLinea($html, 0, $porGramaje->id))->toBeTrue()
        ->and(productoElegidoEnLinea($html, 1, $porUnidad->id))->toBeTrue()
        ->and(valorDelCampo($html, 'lineas[0][cantidad]'))->toBe('0.750')
        ->and(valorDelCampo($html, 'lineas[1][cantidad]'))->toBe('2.5')
        // Dos líneas del formulario más el molde que usa el botón "Agregar producto".
        ->and(substr_count($html, '<div data-linea-venta'))->toBe(3);
});

/*
 * Formulario de registro.
 */

test('el formulario de registro pide cliente y productos, sin elegir la factura', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'María López']);
    $porUnidad = Producto::factory()->create(['descripcion' => 'Gaseosa', 'precio' => 6500]);
    $porGramaje = Producto::factory()->conGramaje()->create(['descripcion' => 'Arroz', 'precio' => 4500]);

    $html = $this->get(route('ventas.create'))
        ->assertOk()
        ->assertViewIs('ventas.create')
        ->assertViewHas('clientes')
        ->assertViewHas('productos')
        ->assertSee(route('ventas.store'), false)
        ->assertSee('name="_token"', false)
        ->assertSee('name="cliente_id"', false)
        ->assertSee('María López')
        ->assertSee('Al guardar se genera automáticamente la factura')
        ->assertSee('name="lineas[0][producto_id]"', false)
        ->assertSee('name="lineas[0][cantidad]"', false)
        ->assertSee('Registrar venta')
        ->assertDontSee('name="factura_id"', false)
        ->assertSee(route('clientes.create'), false)
        ->getContent();

    expect($html)->toMatch('/<option value="'.$porUnidad->id.'" data-precio="6500(\.00)?"\s+data-gramaje="0"/')
        ->and($html)->toMatch('/<option value="'.$porGramaje->id.'" data-precio="4500(\.00)?"\s+data-gramaje="1"/')
        // Una línea para empezar más el molde del botón "Agregar producto".
        ->and(substr_count($html, '<div data-linea-venta'))->toBe(2)
        ->and($html)->toContain('<template id="venta-linea-plantilla">')
        ->and($html)->toContain('name="lineas[__INDICE__][producto_id]"');
});

test('el formulario de registro ordena los clientes y los productos por nombre', function () {
    $this->actingAs(Usuario::factory()->cajero()->create());
    Cliente::factory()->create(['nombre' => 'Zoila']);
    Cliente::factory()->create(['nombre' => 'Ana']);
    Producto::factory()->create(['descripcion' => 'Sal']);
    Producto::factory()->create(['descripcion' => 'Arroz']);

    $this->get(route('ventas.create'))
        ->assertViewHas('clientes', fn ($clientes) => $clientes->pluck('nombre')->all() === ['Ana', 'Zoila'])
        ->assertViewHas('productos', fn ($productos) => $productos->pluck('descripcion')->all() === ['Arroz', 'Sal']);
});

/*
 * Listado, detalle, edición y eliminación de una venta.
 */

test('el listado muestra cada venta con su factura y la cantidad sin ceros sobrantes', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $unidad = Venta::factory()->create(['cantidad' => 3]);
    $peso = Venta::factory()->create(['producto_id' => Producto::factory()->conGramaje()->create()->id, 'cantidad' => 1.5]);

    $this->get(route('ventas.index'))
        ->assertOk()
        ->assertSee('Nueva Venta')
        ->assertSee('<td class="whitespace-nowrap">3</td>', false)
        ->assertSee('<td class="whitespace-nowrap">1,5</td>', false)
        ->assertSee(route('facturas.show', $unidad->factura_id), false)
        ->assertSee(route('ventas.show', $peso), false);
});

test('muestra el detalle de una venta con su factura, cliente, producto y subtotal', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $factura = Factura::factory()->create(['fecha' => '2026-11-03', 'cliente_id' => $cliente->id]);
    $producto = Producto::factory()->conGramaje()->create(['descripcion' => 'Arroz Diana 500 g', 'precio' => 2500.50]);
    $venta = Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $producto->id, 'cantidad' => 2]);

    $this->get(route('ventas.show', $venta))
        ->assertOk()
        ->assertViewIs('ventas.show')
        ->assertViewHas('venta', fn ($vista) => $vista->is($venta)
            && $vista->relationLoaded('factura')
            && $vista->relationLoaded('producto'))
        ->assertSee('#'.$factura->id.' · 03/11/2026')
        ->assertSee('Juan Pérez')
        ->assertSee('Arroz Diana 500 g')
        ->assertSee('$ 2.500,50', false)
        ->assertSee('$ 5.001,00', false)
        ->assertSee(route('facturas.show', $factura), false)
        ->assertSee(route('clientes.show', $cliente), false)
        ->assertSee(route('productos.show', $producto), false);
});

test('el formulario de edición muestra la factura de la venta sin permitir cambiarla', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $cliente = Cliente::factory()->create(['nombre' => 'Juan Pérez']);
    $factura = Factura::factory()->create(['fecha' => '2026-11-03', 'cliente_id' => $cliente->id]);
    $producto = Producto::factory()->conGramaje()->create();
    $venta = Venta::factory()->create(['factura_id' => $factura->id, 'producto_id' => $producto->id, 'cantidad' => 1.25]);

    $html = $this->get(route('ventas.edit', $venta))
        ->assertOk()
        ->assertViewIs('ventas.edit')
        ->assertSee('#'.$factura->id.' · 03/11/2026')
        ->assertSee('Juan Pérez')
        ->assertSee('name="_method" value="PUT"', false)
        ->assertSee(route('ventas.update', $venta), false)
        ->assertDontSee('name="factura_id"', false)
        ->getContent();

    expect(valorDelCampo($html, 'cantidad'))->toBe('1.25')
        ->and($html)->toMatch('/<option value="'.$producto->id.'"[^>]*\sselected[\s>]/');
});

test('el formulario de edición muestra una cantidad entera sin decimales', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create(['cantidad' => 100]);

    expect(valorDelCampo($this->get(route('ventas.edit', $venta))->getContent(), 'cantidad'))->toBe('100');
});

test('corrige el producto y la cantidad de una venta y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create(['cantidad' => 1]);
    $facturaOriginal = $venta->factura_id;
    $nuevoProducto = Producto::factory()->create();

    $this->put(route('ventas.update', $venta), [
        'producto_id' => $nuevoProducto->id,
        'cantidad' => '7',
        'factura_id' => Factura::factory()->create()->id,
    ])
        ->assertRedirect(route('ventas.index'))
        ->assertSessionHas('success', 'Venta actualizada correctamente.');

    $venta->refresh();

    expect($venta->producto_id)->toBe($nuevoProducto->id)
        ->and((float) $venta->cantidad)->toBe(7.0)
        ->and($venta->factura_id)->toBe($facturaOriginal);
});

test('al corregir una venta también se aplica la regla del gramaje', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create(['cantidad' => 2]);
    $porGramaje = Producto::factory()->conGramaje()->create();
    $porUnidad = Producto::factory()->create(['descripcion' => 'Gaseosa']);

    $this->put(route('ventas.update', $venta), ['producto_id' => $porGramaje->id, 'cantidad' => '0.750'])
        ->assertSessionHasNoErrors();
    expect((float) $venta->fresh()->cantidad)->toBe(0.75);

    $this->put(route('ventas.update', $venta), ['producto_id' => $porUnidad->id, 'cantidad' => '0.750'])
        ->assertSessionHasErrors([
            'cantidad' => '«Gaseosa» no se vende por gramaje: la cantidad debe ser un número entero.',
        ]);
    expect($venta->fresh()->producto_id)->toBe($porGramaje->id)
        ->and((float) $venta->fresh()->cantidad)->toBe(0.75);
});

test('rechaza datos inválidos al corregir una venta', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create(['cantidad' => 3]);

    $this->put(route('ventas.update', $venta), datosValidosParaEditarVenta($cambios))->assertSessionHasErrors($campo);

    expect((float) $venta->fresh()->cantidad)->toBe(3.0);
})->with([
    'sin producto' => [['producto_id' => ''], 'producto_id'],
    'producto inexistente' => [['producto_id' => 999999], 'producto_id'],
    'sin cantidad' => [['cantidad' => ''], 'cantidad'],
    'cantidad negativa' => [['cantidad' => '-1'], 'cantidad'],
    'cantidad en cero' => [['cantidad' => '0'], 'cantidad'],
    'cantidad decimal en un producto por unidades' => [['cantidad' => '1.5'], 'cantidad'],
]);

test('muestra los errores y conserva lo escrito cuando falla la validación al corregir', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create();
    $producto = Producto::factory()->create();

    $html = $this->from(route('ventas.edit', $venta))
        ->followingRedirects()
        ->put(route('ventas.update', $venta), ['producto_id' => $producto->id, 'cantidad' => ''])
        ->assertOk()
        ->assertViewIs('ventas.edit')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El campo cantidad es obligatorio.')
        ->getContent();

    expect($html)->toMatch('/<option value="'.$producto->id.'"[^>]*\sselected[\s>]/');
});

test('elimina una venta (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create();

    $this->delete(route('ventas.destroy', $venta))
        ->assertRedirect(route('ventas.index'))
        ->assertSessionHas('success', 'Venta eliminada correctamente.');

    $this->assertSoftDeleted($venta);
    $this->assertNotSoftDeleted($venta->factura);
});

test('al eliminar la única venta de un producto, este vuelve a poder eliminarse', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $venta = Venta::factory()->create();

    $this->delete(route('productos.destroy', $venta->producto_id))->assertSessionHas('error');

    $this->delete(route('ventas.destroy', $venta));

    $this->delete(route('productos.destroy', $venta->producto_id))->assertSessionHas('success');
});

test('mostrar, corregir o eliminar una venta inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->get(route('ventas.show', 999999))->assertNotFound();
    $this->put(route('ventas.update', 999999), datosValidosParaEditarVenta())->assertNotFound();
    $this->delete(route('ventas.destroy', 999999))->assertNotFound();
});

test('la cantidad se muestra sin decimales si es entera y sin ceros sobrantes si no lo es', function (float $cantidad, string $esperada) {
    expect((new Venta(['cantidad' => $cantidad]))->cantidad_formateada)->toBe($esperada);
})->with([
    'entera' => [2, '2'],
    'una cifra decimal' => [1.5, '1,5'],
    'dos cifras decimales' => [0.25, '0,25'],
    'tres cifras decimales' => [0.001, '0,001'],
    'millar entero' => [1000, '1.000'],
    'millar con decimales' => [1250.125, '1.250,125'],
    'centena entera' => [100, '100'],
]);
