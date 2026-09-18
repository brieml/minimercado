<x-layouts.app>
    <div class="flex max-w-lg flex-col gap-6">
        <flux:heading size="xl">Editar proveedor</flux:heading>

        <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            @include('proveedores.form')

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">Guardar cambios</flux:button>
                <flux:button :href="route('proveedores.index')" variant="ghost">Cancelar</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
