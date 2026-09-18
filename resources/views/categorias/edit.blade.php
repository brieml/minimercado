<x-layouts.app>
    <div class="flex max-w-lg flex-col gap-6">
        <flux:heading size="xl">Editar categoría</flux:heading>

        <form method="POST" action="{{ route('categorias.update', $categoria) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            @include('categorias.form')

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Guardar cambios</flux:button>
                <flux:button :href="route('categorias.index')" variant="ghost">Cancelar</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
