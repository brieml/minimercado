<x-layouts.app>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl">Usuarios</flux:heading>
            <flux:button :href="route('usuarios.create')" variant="primary" icon="plus">Nuevo usuario</flux:button>
        </div>

        <form method="GET" action="{{ route('usuarios.index') }}" class="max-w-sm">
            <flux:input type="search" name="buscar" placeholder="Buscar por nombre o correo..." value="{{ request('buscar') }}" icon="magnifying-glass" />
        </form>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Nombre</flux:table.column>
                <flux:table.column>Correo</flux:table.column>
                <flux:table.column>Rol</flux:table.column>
                <flux:table.column>Estado</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($usuarios as $usuario)
                    <flux:table.row>
                        <flux:table.cell>{{ $usuario->name }}</flux:table.cell>
                        <flux:table.cell>{{ $usuario->correo }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge color="blue" size="sm">{{ $usuario->rol }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$usuario->estado === 'Activo' ? 'green' : 'zinc'" size="sm">{{ $usuario->estado }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="flex justify-end gap-2">
                            <flux:button :href="route('usuarios.edit', $usuario)" size="sm" icon="pencil" variant="ghost" />

                            @unless ($usuario->is(auth()->user()))
                                <flux:modal.trigger name="eliminar-usuario-{{ $usuario->id }}">
                                    <flux:button size="sm" icon="trash" variant="ghost" />
                                </flux:modal.trigger>

                                <flux:modal name="eliminar-usuario-{{ $usuario->id }}" class="min-w-[22rem]">
                                    <div class="flex flex-col gap-4">
                                        <flux:heading size="lg">¿Eliminar usuario?</flux:heading>
                                        <flux:text>Se eliminará <strong>{{ $usuario->name }}</strong>. Esta acción se puede revertir por un administrador.</flux:text>
                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Cancelar</flux:button>
                                            </flux:modal.close>
                                            <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" variant="danger">Eliminar</flux:button>
                                            </form>
                                        </div>
                                    </div>
                                </flux:modal>
                            @endunless
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center text-zinc-500">No hay usuarios registrados.</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $usuarios->links() }}
    </div>
</x-layouts.app>
