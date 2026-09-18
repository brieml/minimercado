<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Categorías</flux:heading>
            <flux:button :href="route('categorias.create')" variant="primary" icon="plus">Nueva categoría</flux:button>
        </div>

        <form method="GET" action="{{ route('categorias.index') }}" class="max-w-sm">
            <flux:input type="search" name="buscar" placeholder="Buscar categoría..." value="{{ request('buscar') }}" icon="magnifying-glass" />
        </form>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Descripción</flux:table.column>
                <flux:table.column>Creada</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($categorias as $categoria)
                    <flux:table.row>
                        <flux:table.cell>{{ $categoria->descripcion }}</flux:table.cell>
                        <flux:table.cell>{{ $categoria->created_at->format('d/m/Y') }}</flux:table.cell>
                        <flux:table.cell class="flex justify-end gap-2">
                            <flux:button :href="route('categorias.edit', $categoria)" size="sm" icon="pencil" variant="ghost" />

                            <flux:modal.trigger name="eliminar-categoria-{{ $categoria->id }}">
                                <flux:button size="sm" icon="trash" variant="ghost" />
                            </flux:modal.trigger>

                            <flux:modal name="eliminar-categoria-{{ $categoria->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-4">
                                    <flux:heading size="lg">¿Eliminar categoría?</flux:heading>
                                    <flux:text>Se eliminará <strong>{{ $categoria->descripcion }}</strong>. Esta acción se puede revertir por un administrador.</flux:text>
                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Cancelar</flux:button>
                                        </flux:modal.close>
                                        <form method="POST" action="{{ route('categorias.destroy', $categoria) }}">
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
                        <flux:table.cell colspan="3" class="text-center text-zinc-500">No hay categorías registradas.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $categorias->links() }}
    </div>
</x-layouts.app>
