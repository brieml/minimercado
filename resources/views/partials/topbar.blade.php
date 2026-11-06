<header
    class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false"
        class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
        <span class="sr-only">Abrir menú</span>
        <flux:icon.bars-3 class="size-6" />
    </button>

    <div class="ml-auto flex items-center gap-3">
        <div class="hidden text-right sm:block">
            <p class="text-sm font-medium leading-tight text-slate-900">{{ auth()->user()->nombre_completo }}</p>
            <p class="text-xs text-slate-500">{{ auth()->user()->rol }}</p>
        </div>

        <span class="flex size-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"
            aria-hidden="true">
            {{ auth()->user()->iniciales }}
        </span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                <flux:icon.arrow-right-start-on-rectangle class="size-5" />
                <span class="hidden sm:inline">Cerrar sesión</span>
            </button>
        </form>
    </div>
</header>
