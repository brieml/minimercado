@php
    $mensajes = collect(['success' => session('success'), 'error' => session('error')])->filter();
@endphp

@if ($mensajes->isNotEmpty())
    <div class="pointer-events-none fixed bottom-4 left-4 right-4 z-50 flex flex-col gap-3 sm:left-auto sm:w-full sm:max-w-sm" aria-live="polite">
        @foreach ($mensajes as $tipo => $mensaje)
            <div data-flash="{{ $tipo }}" role="{{ $tipo === 'error' ? 'alert' : 'status' }}"
                @class([
                    'pointer-events-auto flex items-start gap-3 rounded-lg border p-4 shadow-lg transition duration-300',
                    'border-emerald-200 bg-emerald-50 text-emerald-900' => $tipo === 'success',
                    'border-red-200 bg-red-50 text-red-900' => $tipo === 'error',
                ])>
                <flux:icon :name="$tipo === 'success' ? 'check-circle' : 'exclamation-triangle'"
                    :class="$tipo === 'success' ? 'mt-0.5 size-5 shrink-0 text-emerald-600' : 'mt-0.5 size-5 shrink-0 text-red-600'" />

                <p class="flex-1 text-sm font-medium">{{ $mensaje }}</p>

                <button type="button" data-flash-close class="-m-1 rounded p-1 opacity-60 hover:opacity-100">
                    <span class="sr-only">Cerrar mensaje</span>
                    <flux:icon.x-mark class="size-4" />
                </button>
            </div>
        @endforeach
    </div>
@endif
