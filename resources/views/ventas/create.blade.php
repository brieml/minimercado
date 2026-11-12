@extends('layouts.app')

@section('title', 'Crear Venta')

@section('content')
    <x-page-header title="Crear Venta" subtitle="Registra un producto vendido dentro de una factura" />

    <x-card class="max-w-3xl">
        <form action="{{ route('ventas.store') }}" method="POST" novalidate>
            @csrf

            @include('ventas.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('ventas.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
