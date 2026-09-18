<x-layouts.app>
    <div class="flex max-w-2xl flex-col gap-6">
        <flux:heading size="xl">Editar venta</flux:heading>

        <form method="POST" action="{{ route('ventas.update', $factura) }}">
            @csrf
            @method('PUT')

            @include('ventas.form')

            <div class="mt-6 flex gap-3">
                <flux:button type="submit" variant="primary">Guardar cambios</flux:button>
                <flux:button :href="route('ventas.show', $factura)" variant="ghost">Cancelar</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
