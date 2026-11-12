{{--
    Producto y cantidad de una venta.
    - Con $indice: una línea de la venta nueva (campos lineas[$indice][...]), con subtotal y botón para quitarla.
    - Sin $indice: la edición de una sola venta (campos producto_id y cantidad).
    Recibe $productos y, opcionalmente, $linea = ['producto_id' => ..., 'cantidad' => ...].
--}}
@php
    $enVenta = isset($indice);
    $nombreProducto = $enVenta ? "lineas[$indice][producto_id]" : 'producto_id';
    $nombreCantidad = $enVenta ? "lineas[$indice][cantidad]" : 'cantidad';
    $claveProducto = $enVenta ? "lineas.$indice.producto_id" : 'producto_id';
    $claveCantidad = $enVenta ? "lineas.$indice.cantidad" : 'cantidad';
    $idProducto = 'producto-'.($indice ?? 'unico');
    $idCantidad = 'cantidad-'.($indice ?? 'unica');
    $productoElegido = (string) ($linea['producto_id'] ?? '');

    $control = 'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2';
    $normal = 'border-slate-300 focus:border-blue-600 focus:ring-blue-600/30';
    $erroneo = 'border-red-500 focus:border-red-600 focus:ring-red-600/20';
@endphp

<div data-linea-venta
    @class([
        'grid gap-3',
        'rounded-lg border border-slate-200 bg-slate-50/70 p-3 sm:grid-cols-[minmax(0,1fr)_11rem_9rem_2.5rem] sm:items-start' => $enVenta,
        'sm:grid-cols-2' => ! $enVenta,
    ])>
    <div>
        <label for="{{ $idProducto }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            Producto <span class="text-red-600" aria-hidden="true">*</span>
        </label>

        <select id="{{ $idProducto }}" name="{{ $nombreProducto }}" data-producto
            @if ($errors->has($claveProducto)) aria-invalid="true" @endif
            @class([$control, $normal => ! $errors->has($claveProducto), $erroneo => $errors->has($claveProducto)])>
            <option value="">Seleccione un producto</option>

            @foreach ($productos as $producto)
                <option value="{{ $producto->id }}" data-precio="{{ $producto->precio }}"
                    data-gramaje="{{ $producto->tiene_gramaje ? 1 : 0 }}"
                    @selected((string) $producto->id === $productoElegido)>{{ $producto->descripcion }} · $ {{ number_format($producto->precio, 2, ',', '.') }}</option>
            @endforeach
        </select>

        @error($claveProducto)
            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $idCantidad }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            Cantidad <span class="text-red-600" aria-hidden="true">*</span>
        </label>

        <input id="{{ $idCantidad }}" name="{{ $nombreCantidad }}" type="number" inputmode="decimal" min="1" step="1"
            value="{{ $linea['cantidad'] ?? '' }}" data-cantidad
            @if ($errors->has($claveCantidad)) aria-invalid="true" @endif
            @class([$control, $normal => ! $errors->has($claveCantidad), $erroneo => $errors->has($claveCantidad)])>

        <p class="mt-1 text-xs text-slate-500" data-ayuda-cantidad>Elige primero el producto</p>

        @error($claveCantidad)
            <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($enVenta)
        <div class="flex items-center justify-between gap-2 sm:block sm:pt-9 sm:text-right">
            <span class="text-xs text-slate-500 sm:hidden">Subtotal</span>
            <span class="text-sm font-medium text-slate-900">$ <span data-subtotal>0,00</span></span>
        </div>

        <div class="sm:pt-7">
            <button type="button" data-quitar-linea
                class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-slate-500 hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 sm:size-9 sm:justify-center sm:p-0">
                <flux:icon.trash class="size-5" />
                <span class="sm:sr-only">Quitar producto</span>
            </button>
        </div>
    @endif
</div>
