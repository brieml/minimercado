@extends('layouts.app')

@section('title', 'Editar Venta')

@section('content')
    <x-page-header title="Editar Venta" subtitle="Modifica los datos de la venta seleccionada" />

    <x-card class="max-w-3xl">
        <form action="{{ route('ventas.update', $venta) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            @include('ventas.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('ventas.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
