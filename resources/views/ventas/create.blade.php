@extends('layouts.app')

@section('title', 'Registrar Venta')

@section('content')
    @php
        // Líneas de la venta: lo que se escribió si falló la validación o una línea vacía para empezar.
        $lineas = old('lineas', [[]]);
    @endphp

    <x-page-header title="Registrar Venta" subtitle="Al guardar se genera automáticamente la factura del cliente con los productos vendidos" />

    <x-card class="max-w-4xl">
        <form id="venta-form" action="{{ route('ventas.store') }}" method="POST" novalidate>
            @csrf

            <x-form.errors />

            <div class="max-w-md">
                <x-form.select name="cliente_id" label="Cliente" :options="$clientes->pluck('nombre', 'id')" required />

                @if (auth()->user()->puedeAccederA('clientes'))
                    <p class="mt-1.5 text-xs text-slate-500">
                        ¿El cliente no está en la lista?
                        <a href="{{ route('clientes.create') }}" class="font-medium text-blue-600 hover:underline">Regístralo aquí</a>.
                    </p>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-slate-900">Productos vendidos</h2>

                <x-button type="button" variant="secondary" size="sm" data-agregar-linea>
                    <flux:icon.plus class="size-4" />
                    Agregar producto
                </x-button>
            </div>

            @error('lineas')
                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror

            <div id="venta-lineas" class="mt-3 space-y-3">
                @foreach ($lineas as $indice => $linea)
                    @include('ventas.linea', ['indice' => $indice, 'linea' => $linea])
                @endforeach
            </div>

            <div class="mt-5 flex items-center justify-end gap-4 border-t border-slate-200 pt-4">
                <span class="text-sm font-medium text-slate-500">Total de la venta</span>
                <span class="text-2xl font-semibold tracking-tight text-slate-900" aria-live="polite">$ <span data-total-venta>0,00</span></span>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Registrar venta</x-button>
                <x-button :href="route('ventas.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>

    {{-- Molde de una línea nueva: el script lo copia al pulsar "Agregar producto". --}}
    <template id="venta-linea-plantilla">
        @include('ventas.linea', ['indice' => '__INDICE__', 'linea' => []])
    </template>
@endsection
