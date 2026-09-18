<x-layouts.app>
    <div class="flex max-w-lg flex-col gap-6">
        <flux:heading size="xl">Editar usuario</flux:heading>

        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            @include('usuarios.form')

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Guardar cambios</flux:button>
                <flux:button :href="route('usuarios.index')" variant="ghost">Cancelar</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
