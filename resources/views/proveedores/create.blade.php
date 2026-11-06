@extends('layouts.app')

@section('title', 'Crear Proveedor')

@section('content')
    <x-page-header title="Crear Proveedor" subtitle="Registra un nuevo proveedor del minimercado" />

    <x-card class="max-w-2xl">
        <form action="{{ route('proveedores.store') }}" method="POST">
            @csrf

            @include('proveedores.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('proveedores.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
