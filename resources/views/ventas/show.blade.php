<x-layouts.app>
    <div class="flex max-w-2xl flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Venta #{{ $factura->id }}</flux:heading>
            <div class="flex gap-2">
                <flux:button :href="route('ventas.edit', $factura)" icon="pencil" variant="ghost">Editar</flux:button>
                <flux:button :href="route('ventas.index')" variant="ghost">Volver</flux:button>
            </div>
        </div>

        <flux:card class="flex flex-col gap-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <flux:text size="sm">Fecha</flux:text>
                    <flux:heading>{{ $factura->fecha->format('d/m/Y') }}</flux:heading>
                </div>
                <div>
                    <flux:text size="sm">Cliente</flux:text>
                    <flux:heading>{{ $factura->cliente->nombre }}</flux:heading>
                </div>
                <div>
                    <flux:text size="sm">Vendedor</flux:text>
                    <flux:heading>{{ $factura->usuario->name }}</flux:heading>
                </div>
            </div>

            <flux:separator />

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Producto</flux:table.column>
                    <flux:table.column>Cantidad</flux:table.column>
                    <flux:table.column>Precio</flux:table.column>
                    <flux:table.column>Subtotal</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($factura->ventas as $venta)
                        <flux:table.row>
                            <flux:table.cell>{{ $venta->producto->descripcion }}</flux:table.cell>
                            <flux:table.cell><x-cantidad :valor="$venta->cantidad" /></flux:table.cell>
                            <flux:table.cell><x-precio :valor="$venta->producto->precio" /></flux:table.cell>
                            <flux:table.cell><x-precio :valor="$venta->subtotal()" /></flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="flex justify-end gap-2 text-lg">
                <span class="text-zinc-500">Total:</span>
                <span class="font-semibold"><x-precio :valor="$factura->total()" /></span>
            </div>
        </flux:card>
    </div>
</x-layouts.app>
