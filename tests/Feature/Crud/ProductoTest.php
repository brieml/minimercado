<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Venta;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Datos válidos para el formulario de productos. La categoría y el proveedor
 * solo se crean cuando la prueba no los sobrescribe.
 *
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function datosValidosDeProducto(array $cambios = []): array
{
    return array_merge([
        'descripcion' => 'Arroz Diana 500 g',
        'precio' => '2500.50',
        'categoria_id' => $cambios['categoria_id'] ?? Categoria::factory()->create()->id,
        'proveedor_id' => $cambios['proveedor_id'] ?? Proveedor::factory()->create()->id,
    ], $cambios);
}

test('los invitados no pueden crear, actualizar ni eliminar productos', function () {
    $producto = Producto::factory()->create(['descripcion' => 'Leche entera']);

    $this->post(route('productos.store'), datosValidosDeProducto())->assertRedirect(route('login'));
    $this->put(route('productos.update', $producto), datosValidosDeProducto())->assertRedirect(route('login'));
    $this->delete(route('productos.destroy', $producto))->assertRedirect(route('login'));

    $this->assertDatabaseMissing('productos', ['descripcion' => 'Arroz Diana 500 g']);
    $this->assertDatabaseHas('productos', ['id' => $producto->id, 'descripcion' => 'Leche entera', 'deleted_at' => null]);
});

test('crea un producto y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $datos = datosValidosDeProducto();

    $this->post(route('productos.store'), $datos)
        ->assertRedirect(route('productos.index'))
        ->assertSessionHas('success', 'Producto creado correctamente.');

    $this->assertDatabaseHas('productos', $datos);
});

test('el listado muestra el mensaje de éxito después de crear', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->followingRedirects()
        ->post(route('productos.store'), datosValidosDeProducto())
        ->assertOk()
        ->assertViewIs('productos.index')
        ->assertSee('data-flash="success"', false)
        ->assertSee('Producto creado correctamente.')
        ->assertSee('Arroz Diana 500 g');
});

test('acepta precios enteros, con uno o dos decimales y el máximo que cabe en la columna', function (string $precio) {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('productos.store'), datosValidosDeProducto(['precio' => $precio]))
        ->assertSessionHasNoErrors();

    expect(Producto::count())->toBe(1)
        ->and((float) Producto::first()->precio)->toBe((float) $precio);
})->with(['0', '1500', '1500.5', '1500.50', '9999999999.99']);

test('rechaza datos inválidos al crear un producto', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('productos.store'), datosValidosDeProducto($cambios))->assertSessionHasErrors($campo);

    expect(Producto::count())->toBe(0);
})->with([
    'sin descripción' => [['descripcion' => ''], 'descripcion'],
    'descripción de más de 150 caracteres' => [['descripcion' => str_repeat('a', 151)], 'descripcion'],
    'sin precio' => [['precio' => ''], 'precio'],
    'precio no numérico' => [['precio' => 'abc'], 'precio'],
    'precio negativo' => [['precio' => '-5'], 'precio'],
    'precio con tres decimales' => [['precio' => '10.555'], 'precio'],
    'precio por encima del máximo' => [['precio' => '10000000000'], 'precio'],
    'sin categoría' => [['categoria_id' => ''], 'categoria_id'],
    'categoría inexistente' => [['categoria_id' => 999999], 'categoria_id'],
    'sin proveedor' => [['proveedor_id' => ''], 'proveedor_id'],
    'proveedor inexistente' => [['proveedor_id' => 999999], 'proveedor_id'],
]);

test('explica con un mensaje claro que el precio no admite más de dos decimales', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->post(route('productos.store'), datosValidosDeProducto(['precio' => '10.555']))
        ->assertSessionHasErrors(['precio' => 'El precio no puede tener más de 2 decimales.']);
});

test('no acepta una categoría ni un proveedor que ya fueron eliminados', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $categoriaEliminada = Categoria::factory()->create();
    $proveedorEliminado = Proveedor::factory()->create();
    $categoriaEliminada->delete();
    $proveedorEliminado->delete();

    $this->post(route('productos.store'), datosValidosDeProducto([
        'categoria_id' => $categoriaEliminada->id,
        'proveedor_id' => $proveedorEliminado->id,
    ]))->assertSessionHasErrors(['categoria_id', 'proveedor_id']);

    expect(Producto::count())->toBe(0);
});

test('muestra los errores y conserva lo escrito cuando falla la validación al crear', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $datos = datosValidosDeProducto(['precio' => 'abc']);

    $this->from(route('productos.create'))
        ->followingRedirects()
        ->post(route('productos.store'), $datos)
        ->assertOk()
        ->assertViewIs('productos.create')
        ->assertSee('Corrige los siguientes errores')
        ->assertSee('El campo precio debe ser un número.')
        ->assertSee('value="Arroz Diana 500 g"', false)
        ->assertSee('value="abc"', false)
        ->assertSee('<option value="'.$datos['categoria_id'].'" selected>', false)
        ->assertSee('<option value="'.$datos['proveedor_id'].'" selected>', false);
});

test('pide elegir categoría y proveedor cuando se envían vacíos', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->from(route('productos.create'))
        ->followingRedirects()
        ->post(route('productos.store'), datosValidosDeProducto(['categoria_id' => '', 'proveedor_id' => '']))
        ->assertOk()
        ->assertSee('El campo categoría es obligatorio.')
        ->assertSee('El campo proveedor es obligatorio.');
});

test('muestra el detalle de un producto con su categoría, proveedor y ventas', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $categoria = Categoria::factory()->create(['descripcion' => 'Granos']);
    $proveedor = Proveedor::factory()->create(['nombre' => 'Distribuidora Sucre']);
    $producto = Producto::factory()->create([
        'descripcion' => 'Arroz Diana 500 g',
        'precio' => 2500.5,
        'categoria_id' => $categoria->id,
        'proveedor_id' => $proveedor->id,
    ]);
    Venta::factory()->count(2)->create(['producto_id' => $producto->id]);

    $this->get(route('productos.show', $producto))
        ->assertOk()
        ->assertViewIs('productos.show')
        ->assertViewHas('producto', fn ($vista) => $vista->is($producto)
            && $vista->relationLoaded('categoria')
            && $vista->relationLoaded('proveedor')
            && (int) $vista->ventas_count === 2)
        ->assertSee('Arroz Diana 500 g')
        ->assertSee('$ 2.500,50', false)
        ->assertSee('Granos')
        ->assertSee('Distribuidora Sucre')
        ->assertSee(route('categorias.show', $categoria), false)
        ->assertSee(route('proveedores.show', $proveedor), false);
});

test('el listado ofrece el enlace al detalle de cada producto', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $producto = Producto::factory()->create();

    $this->get(route('productos.index'))->assertSee(route('productos.show', $producto), false);
});

test('actualiza un producto y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $producto = Producto::factory()->create(['descripcion' => 'Leche entera', 'precio' => 3000]);
    $datos = datosValidosDeProducto(['descripcion' => 'Leche deslactosada', 'precio' => '3200.00']);

    $this->put(route('productos.update', $producto), $datos)
        ->assertRedirect(route('productos.index'))
        ->assertSessionHas('success', 'Producto actualizado correctamente.');

    $producto->refresh();

    expect($producto->descripcion)->toBe('Leche deslactosada')
        ->and((float) $producto->precio)->toBe(3200.0)
        ->and($producto->categoria_id)->toBe($datos['categoria_id'])
        ->and($producto->proveedor_id)->toBe($datos['proveedor_id']);
});

test('rechaza datos inválidos al actualizar un producto', function (array $cambios, string $campo) {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $producto = Producto::factory()->create(['descripcion' => 'Leche entera', 'precio' => 3000]);

    $this->put(route('productos.update', $producto), datosValidosDeProducto($cambios))->assertSessionHasErrors($campo);

    expect($producto->fresh()->descripcion)->toBe('Leche entera')
        ->and((float) $producto->fresh()->precio)->toBe(3000.0);
})->with([
    'sin descripción' => [['descripcion' => ''], 'descripcion'],
    'precio negativo' => [['precio' => '-1'], 'precio'],
    'categoría inexistente' => [['categoria_id' => 999999], 'categoria_id'],
    'proveedor inexistente' => [['proveedor_id' => 999999], 'proveedor_id'],
]);

test('muestra los errores cuando falla la validación al actualizar', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $producto = Producto::factory()->create();

    $this->from(route('productos.edit', $producto))
        ->followingRedirects()
        ->put(route('productos.update', $producto), datosValidosDeProducto(['descripcion' => '']))
        ->assertOk()
        ->assertViewIs('productos.edit')
        ->assertSee('El campo descripción es obligatorio.');
});

test('elimina un producto (borrado lógico) y confirma con un mensaje de sesión', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $producto = Producto::factory()->create();

    $this->delete(route('productos.destroy', $producto))
        ->assertRedirect(route('productos.index'))
        ->assertSessionHas('success', 'Producto eliminado correctamente.');

    $this->assertSoftDeleted($producto);
});

test('no elimina un producto que tiene ventas y avisa con un mensaje de error', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());
    $producto = Producto::factory()->create();
    Venta::factory()->create(['producto_id' => $producto->id]);

    $this->followingRedirects()
        ->delete(route('productos.destroy', $producto))
        ->assertSee('data-flash="error"', false)
        ->assertSee('No se puede eliminar el producto porque tiene ventas relacionadas.');

    $this->assertNotSoftDeleted($producto);
});

test('mostrar, actualizar o eliminar un producto inexistente responde 404', function () {
    $this->actingAs(Usuario::factory()->administrador()->create());

    $this->get(route('productos.show', 999999))->assertNotFound();
    $this->put(route('productos.update', 999999), datosValidosDeProducto())->assertNotFound();
    $this->delete(route('productos.destroy', 999999))->assertNotFound();
});
