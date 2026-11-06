@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('content')
    <x-page-header title="Editar Proveedor" subtitle="Modifica los datos del proveedor seleccionado" />

    <x-card class="max-w-2xl">
        <form action="{{ route('proveedores.update', $proveedor) }}" method="POST">
            @csrf
            @method('PUT')

            @include('proveedores.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('proveedores.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
