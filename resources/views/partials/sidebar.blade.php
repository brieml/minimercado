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

        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Inventario</p>
        <x-nav-link :href="route('productos.index')" :active="request()->routeIs('productos.*')" icon="cube">
            Productos
        </x-nav-link>
        <x-nav-link :href="route('categorias.index')" :active="request()->routeIs('categorias.*')" icon="tag">
            Categorías
        </x-nav-link>
        <x-nav-link :href="route('proveedores.index')" :active="request()->routeIs('proveedores.*')" icon="truck">
            Proveedores
        </x-nav-link>

        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Ventas</p>
        <x-nav-link :href="route('clientes.index')" :active="request()->routeIs('clientes.*')" icon="user-group">
            Clientes
        </x-nav-link>
        <x-nav-link :href="route('facturas.index')" :active="request()->routeIs('facturas.*')" icon="document-text">
            Facturas
        </x-nav-link>
        <x-nav-link :href="route('ventas.index')" :active="request()->routeIs('ventas.*')" icon="shopping-cart">
            Ventas
        </x-nav-link>

        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Administración</p>
        <x-nav-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')" icon="users">
            Usuarios
        </x-nav-link>
    </nav>
</aside>
