@extends('layouts.app')

@section('title', 'Crear Factura')

@section('content')
    <x-page-header title="Crear Factura" subtitle="Registra el encabezado de una nueva venta" />

    <x-card class="max-w-3xl">
        <form action="{{ route('facturas.store') }}" method="POST">
            @csrf

            @include('facturas.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('facturas.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
