@php
    $esEdicion = isset($factura);

    $itemsIniciales = $esEdicion
        ? $factura->ventas->map(fn ($v) => ['producto_id' => $v->producto_id, 'cantidad' => (float) $v->cantidad])->toArray()
        : [['producto_id' => '', 'cantidad' => 1]];

    $itemsIniciales = old('items', $itemsIniciales);
@endphp

<div
    x-data="{
        productos: @js($productos->map(fn ($p) => ['id' => $p->id, 'descripcion' => $p->descripcion, 'precio' => (float) $p->precio])),
        items: @js($itemsIniciales),
        add() { this.items.push({ producto_id: '', cantidad: 1 }) },
        remove(i) { if (this.items.length > 1) this.items.splice(i, 1) },
        precioDe(id) {
            const producto = this.productos.find((p) => p.id == id);
            return producto ? producto.precio : 0;
        },
        subtotal(item) {
            return this.precioDe(item.producto_id) * (Number(item.cantidad) || 0);
        },
        total() {
            return this.items.reduce((suma, item) => suma + this.subtotal(item), 0);
        },
        formatear(valor) {
            return '$' + Math.round(valor).toLocaleString('es-CO');
        },
    }"
    class="flex flex-col gap-6"
>
    @if ($errors->has('items') || $errors->has('items.*.producto_id') || $errors->has('items.*.cantidad'))
        <flux:callout variant="danger" icon="exclamation-triangle" heading="Revisa los productos de la venta." />
    @endif

    <div class="grid grid-cols-2 gap-4">
        <flux:input
            type="date"
            name="fecha"
            label="Fecha"
            value="{{ old('fecha', $esEdicion ? $factura->fecha->format('Y-m-d') : now()->format('Y-m-d')) }}"
            required
        />

        <flux:select name="cliente_id" label="Cliente" required>
            @foreach ($clientes as $cliente)
                <flux:select.option
                    value="{{ $cliente->id }}"
                    :selected="(string) old('cliente_id', $factura->cliente_id ?? $clienteGeneralId ?? '') === (string) $cliente->id"
                >
                    {{ $cliente->nombre }}
                </flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="flex flex-col gap-3">
        <flux:heading size="lg">Productos</flux:heading>

        <template x-for="(item, index) in items" :key="index">
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <flux:label>Producto</flux:label>
                    <select
                        :name="`items[${index}][producto_id]`"
                        x-model="item.producto_id"
                        required
                        class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-800 shadow-xs dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                    >
                        <option value="" disabled>Selecciona un producto</option>
                        <template x-for="producto in productos" :key="producto.id">
                            <option :value="producto.id" x-text="producto.descripcion"></option>
                        </template>
                    </select>
                </div>

                <div class="w-28">
                    <flux:input
                        x-bind:name="`items[${index}][cantidad]`"
                        x-model.number="item.cantidad"
                        type="number"
                        step="0.001"
                        min="0.001"
                        label="Cantidad"
                        required
                    />
                </div>

                <div class="w-28 pb-2.5 text-sm text-zinc-500 dark:text-zinc-400" x-text="formatear(subtotal(item))"></div>

                <flux:button type="button" variant="ghost" icon="trash" x-on:click="remove(index)" x-show="items.length > 1" />
            </div>
        </template>

        <flux:button type="button" variant="ghost" icon="plus" x-on:click="add()" class="self-start">Agregar producto</flux:button>
    </div>

    <div class="flex justify-end gap-2 text-lg">
        <span class="text-zinc-500">Total:</span>
        <span class="font-semibold" x-text="formatear(total())"></span>
    </div>
</div>
