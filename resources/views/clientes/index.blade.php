<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Clientes</flux:heading>
            <flux:button :href="route('clientes.create')" variant="primary" icon="plus">Nuevo cliente</flux:button>
        </div>

        <form method="GET" action="{{ route('clientes.index') }}" class="max-w-sm">
            <flux:input type="search" name="buscar" placeholder="Buscar cliente..." value="{{ request('buscar') }}" icon="magnifying-glass" />
        </form>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Dirección</flux:table.column>
                <flux:table.column>Teléfono</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($clientes as $cliente)
                    <flux:table.row>
                        <flux:table.cell>{{ $cliente->nombre }}</flux:table.cell>
                        <flux:table.cell>{{ $cliente->direccion ?? '—' }}</flux:table.cell>
                        <flux:table.cell>{{ $cliente->telefono ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="flex justify-end gap-2">
                            <flux:button :href="route('clientes.edit', $cliente)" size="sm" icon="pencil" variant="ghost" />

                            <flux:modal.trigger name="eliminar-cliente-{{ $cliente->id }}">
                                <flux:button size="sm" icon="trash" variant="ghost" />
                            </flux:modal.trigger>

                            <flux:modal name="eliminar-cliente-{{ $cliente->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-4">
                                    <flux:heading size="lg">¿Eliminar cliente?</flux:heading>
                                    <flux:text>Se eliminará <strong>{{ $cliente->nombre }}</strong>. Esta acción se puede revertir por un administrador.</flux:text>
                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancelar</flux:button>
                                        </flux:modal.close>
                                        <form method="POST" action="{{ route('clientes.destroy', $cliente) }}">
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
                        <flux:table.cell colspan="4" class="text-center text-zinc-500">No hay clientes registrados.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $clientes->links() }}
    </div>
</x-layouts.app>
