<x-layouts.app>
    <div class="flex max-w-2xl flex-col gap-6">
        <flux:heading size="xl">Nueva venta</flux:heading>

        <form method="POST" action="{{ route('ventas.store') }}">
            @csrf

            @include('ventas.form')

            <div class="mt-6 flex gap-3">
                <flux:button type="submit" variant="primary">Registrar venta</flux:button>
                <flux:button :href="route('ventas.index')" variant="ghost">Cancelar</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
