@php
    $usuarioActual = auth()->user();

    // Grupo del menú => [módulo (nombre del recurso en las rutas), etiqueta, icono].
    // Cada rol solo ve los módulos que tiene en Usuario::MODULOS_POR_ROL.
    $grupos = [
        'Inventario' => [
            ['productos', 'Productos', 'cube'],
            ['categorias', 'Categorías', 'tag'],
            ['proveedores', 'Proveedores', 'truck'],
        ],
        'Ventas' => [
            ['clientes', 'Clientes', 'user-group'],
            ['facturas', 'Facturas', 'document-text'],
            ['ventas', 'Ventas', 'shopping-cart'],
        ],
        'Administración' => [
            ['usuarios', 'Usuarios', 'users'],
        ],
    ];
@endphp

<div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden" aria-hidden="true"></div>

<aside id="sidebar" aria-label="Navegación principal"
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 transition-transform duration-200 lg:translate-x-0">
    <a href="{{ route('dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <span class="flex size-9 items-center justify-center rounded-lg bg-blue-600 text-white">
            <flux:icon.building-storefront class="size-5" />
        </span>
        <span class="text-lg font-semibold tracking-tight text-white">Minimercado</span>
    </a>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="squares-2x2">
            Dashboard
        </x-nav-link>

        @foreach ($grupos as $titulo => $modulos)
            @php
                $visibles = array_filter($modulos, fn (array $modulo) => $usuarioActual->puedeAccederA($modulo[0]));
            @endphp

            @if ($visibles)
                <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $titulo }}</p>

                @foreach ($visibles as [$recurso, $etiqueta, $icono])
                    <x-nav-link :href="route($recurso.'.index')" :active="request()->routeIs($recurso.'.*')" :icon="$icono">
                        {{ $etiqueta }}
                    </x-nav-link>
                @endforeach
            @endif
        @endforeach
    </nav>
</aside>
