@extends('layouts.app')

@section('title', 'Crear Producto')

@section('content')
    <x-page-header title="Crear Producto" subtitle="Registra un nuevo producto en el inventario" />

    <x-card class="max-w-3xl">
        <form action="{{ route('productos.store') }}" method="POST">
            @csrf

            @include('productos.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('productos.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
