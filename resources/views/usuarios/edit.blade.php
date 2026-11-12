@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
    <x-page-header title="Editar Usuario" subtitle="Modifica los datos del usuario seleccionado" />

    <x-card class="max-w-3xl">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            @include('usuarios.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('usuarios.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
