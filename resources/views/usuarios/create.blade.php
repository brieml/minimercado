@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
    <x-page-header title="Crear Usuario" subtitle="Registra una nueva persona con acceso al sistema" />

    <x-card class="max-w-3xl">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf

            @include('usuarios.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('usuarios.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
