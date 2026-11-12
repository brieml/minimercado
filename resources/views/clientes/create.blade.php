@extends('layouts.app')

@section('title', 'Crear Cliente')

@section('content')
    <x-page-header title="Crear Cliente" subtitle="Registra un nuevo cliente del minimercado" />

    <x-card class="max-w-2xl">
        <form action="{{ route('clientes.store') }}" method="POST" novalidate>
            @csrf

            @include('clientes.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('clientes.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
