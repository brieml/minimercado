<x-layouts.app>
    @php $rol = auth()->user()->rol; @endphp

    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <flux:heading size="xl">Hola, {{ auth()->user()->name }}</flux:heading>
                <flux:text class="mt-1">Panel general del Minimercado</flux:text>
            </div>

            <flux:badge color="blue" size="lg">{{ $rol }}</flux:badge>
        </div>

        @if ($ventasHoy)
            <flux:card class="bg-accent/5">
                <flux:heading size="lg">Ventas de hoy</flux:heading>
                <div class="mt-3 flex flex-wrap gap-8">
                    <div>
                        <flux:text size="sm">Facturas</flux:text>
                        <flux:heading size="xl">{{ $ventasHoy['cantidad'] }}</flux:heading>
                    </div>
                    <div>
                        <flux:text size="sm">Total vendido</flux:text>
                        <flux:heading size="xl"><x-precio :valor="$ventasHoy['total']" /></flux:heading>
                    </div>
                </div>
            </flux:card>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tarjetas = [
                    ['titulo' => 'Productos', 'valor' => $productos, 'icono' => 'archive-box', 'ruta' => 'productos.index', 'roles' => ['Administrador', 'Inventario']],
                    ['titulo' => 'Categorías', 'valor' => $categorias, 'icono' => 'tag', 'ruta' => 'categorias.index', 'roles' => ['Administrador', 'Inventario']],
                    ['titulo' => 'Proveedores', 'valor' => $proveedores, 'icono' => 'truck', 'ruta' => 'proveedores.index', 'roles' => ['Administrador', 'Inventario']],
                    ['titulo' => 'Clientes', 'valor' => $clientes, 'icono' => 'user-group', 'ruta' => 'clientes.index', 'roles' => ['Administrador', 'Cajero']],
                ];
            @endphp

            @foreach ($tarjetas as $tarjeta)
                @php $permitido = in_array($rol, $tarjeta['roles']); @endphp

                @if ($permitido)
                    <a href="{{ route($tarjeta['ruta']) }}" wire:navigate>
                        <flux:card class="flex items-center gap-4 transition-colors hover:border-accent">
                            <flux:icon :name="$tarjeta['icono']" class="size-8 text-accent" />
                            <div>
                                <flux:text size="sm">{{ $tarjeta['titulo'] }}</flux:text>
                                <flux:heading size="xl">{{ $tarjeta['valor'] }}</flux:heading>
                            </div>
                        </flux:card>
                    </a>
                @else
                    <flux:card class="flex items-center gap-4 opacity-60">
                        <flux:icon :name="$tarjeta['icono']" class="size-8 text-accent" />
                        <div>
                            <flux:text size="sm">{{ $tarjeta['titulo'] }}</flux:text>
                            <flux:heading size="xl">{{ $tarjeta['valor'] }}</flux:heading>
                        </div>
                    </flux:card>
                @endif
            @endforeach
        </div>
    </div>
</x-layouts.app>
