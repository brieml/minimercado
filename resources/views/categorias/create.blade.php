<x-layouts.app>
    <div class="flex max-w-lg flex-col gap-6">
        <flux:heading size="xl">Nueva categoría</flux:heading>

        <form method="POST" action="{{ route('categorias.store') }}" class="flex flex-col gap-6">
            @csrf

            @include('categorias.form')

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Guardar</flux:button>
                <flux:button :href="route('categorias.index')" variant="ghost">Cancelar</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
