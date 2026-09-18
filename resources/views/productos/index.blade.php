<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Productos</flux:heading>
            <flux:button :href="route('productos.create')" variant="primary" icon="plus">Nuevo producto</flux:button>
        </div>

        <form method="GET" action="{{ route('productos.index') }}" class="max-w-sm">
            <flux:input type="search" name="buscar" placeholder="Buscar producto..." value="{{ request('buscar') }}" icon="magnifying-glass" />
        </form>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Descripción</flux:table.column>
                <flux:table.column>Categoría</flux:table.column>
                <flux:table.column>Proveedor</flux:table.column>
                <flux:table.column>Precio</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($productos as $producto)
                    <flux:table.row>
                        <flux:table.cell>{{ $producto->descripcion }}</flux:table.cell>
                        <flux:table.cell>{{ $producto->categoria->descripcion }}</flux:table.cell>
                        <flux:table.cell>{{ $producto->proveedor->nombre }}</flux:table.cell>
                        <flux:table.cell><x-precio :valor="$producto->precio" /></flux:table.cell>
                        <flux:table.cell class="flex justify-end gap-2">
                            <flux:button :href="route('productos.edit', $producto)" size="sm" icon="pencil" variant="ghost" />

                            <flux:modal.trigger name="eliminar-producto-{{ $producto->id }}">
                                <flux:button size="sm" icon="trash" variant="ghost" />
                            </flux:modal.trigger>

                            <flux:modal name="eliminar-producto-{{ $producto->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-4">
                                    <flux:heading size="lg">¿Eliminar producto?</flux:heading>
                                    <flux:text>Se eliminará <strong>{{ $producto->descripcion }}</strong>. Esta acción se puede revertir por un administrador.</flux:text>
                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancelar</flux:button>
                                        </flux:modal.close>
                                        <form method="POST" action="{{ route('productos.destroy', $producto) }}">
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
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">No hay productos registrados.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $productos->links() }}
    </div>
</x-layouts.app>
