<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Proveedores</flux:heading>
            <flux:button :href="route('proveedores.create')" variant="primary" icon="plus">Nuevo proveedor</flux:button>
        </div>

        <form method="GET" action="{{ route('proveedores.index') }}" class="max-w-sm">
            <flux:input type="search" name="buscar" placeholder="Buscar proveedor..." value="{{ request('buscar') }}" icon="magnifying-glass" />
        </form>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Dirección</flux:table.column>
                <flux:table.column>Teléfono</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($proveedores as $proveedor)
                    <flux:table.row>
                        <flux:table.cell>{{ $proveedor->nombre }}</flux:table.cell>
                        <flux:table.cell>{{ $proveedor->direccion ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $proveedor->telefono ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="flex justify-end gap-2">
                            <flux:button :href="route('proveedores.edit', $proveedor)" size="sm" icon="pencil" variant="ghost" />

                            <flux:modal.trigger name="eliminar-proveedor-{{ $proveedor->id }}">
                                <flux:button size="sm" icon="trash" variant="ghost" />
                            </flux:modal.trigger>

                            <flux:modal name="eliminar-proveedor-{{ $proveedor->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-4">
                                    <flux:heading size="lg">¿Eliminar proveedor?</flux:heading>
                                    <flux:text>Se eliminará <strong>{{ $proveedor->nombre }}</strong>. Esta acción se puede revertir por un administrador.</flux:text>
                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancelar</flux:button>
                                        </flux:modal.close>
                                        <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}">
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
                        <flux:table.cell colspan="4" class="text-center text-zinc-500">No hay proveedores registrados.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $proveedores->links() }}
    </div>
</x-layouts.app>
