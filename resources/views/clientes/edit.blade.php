@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
    <x-page-header title="Editar Cliente" subtitle="Modifica los datos del cliente seleccionado" />

    <x-card class="max-w-2xl">
        <form action="{{ route('clientes.update', $cliente) }}" method="POST">
            @csrf
            @method('PUT')

            @include('clientes.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('clientes.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
