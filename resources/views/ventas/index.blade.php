<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Ventas</flux:heading>
            <flux:button :href="route('ventas.create')" variant="primary" icon="plus">Nueva venta</flux:button>
        </div>

        <form method="GET" action="{{ route('ventas.index') }}" class="max-w-sm">
            <flux:input type="search" name="buscar" placeholder="Buscar por cliente..." value="{{ request('buscar') }}" icon="magnifying-glass" />
        </form>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Fecha</flux:table.column>
                <flux:table.column>Cliente</flux:table.column>
                <flux:table.column>Vendedor</flux:table.column>
                <flux:table.column>Productos</flux:table.column>
                <flux:table.column>Total</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($facturas as $factura)
                    <flux:table.row>
                        <flux:table.cell>{{ $factura->fecha->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $factura->cliente->nombre }}</flux:table.cell>
                        <flux:table.cell>{{ $factura->usuario->name }}</flux:table.cell>
                        <flux:table.cell>{{ $factura->ventas->count() }}</flux:table.cell>
                        <flux:table.cell><x-precio :valor="$factura->total()" /></flux:table.cell>
                        <flux:table.cell class="flex justify-end gap-2">
                            <flux:button :href="route('ventas.show', $factura)" size="sm" icon="eye" variant="ghost" />
                            <flux:button :href="route('ventas.edit', $factura)" size="sm" icon="pencil" variant="ghost" />

                            <flux:modal.trigger name="eliminar-venta-{{ $factura->id }}">
                                <flux:button size="sm" icon="trash" variant="ghost" />
                            </flux:modal.trigger>

                            <flux:modal name="eliminar-venta-{{ $factura->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-4">
                                    <flux:heading size="lg">¿Eliminar venta?</flux:heading>
                                    <flux:text>Se eliminará la venta de <strong>{{ $factura->cliente->nombre }}</strong> del {{ $factura->fecha->format('d/m/Y') }}. Esta acción se puede revertir por un administrador.</flux:text>
                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancelar</flux:button>
                                        </flux:modal.close>
                                        <form method="POST" action="{{ route('ventas.destroy', $factura) }}">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger">Eliminar</flux:button>
                                        </form>
                                    </div>
                                </div>
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center text-zinc-500">No hay ventas registradas.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $facturas->links() }}
    </div>
</x-layouts.app>
