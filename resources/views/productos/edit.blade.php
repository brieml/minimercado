@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
    <x-page-header title="Editar Producto" subtitle="Modifica los datos del producto seleccionado" />

    <x-card class="max-w-3xl">
        <form action="{{ route('productos.update', $producto) }}" method="POST">
            @csrf
            @method('PUT')

            @include('productos.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('productos.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
