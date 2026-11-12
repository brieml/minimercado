@extends('layouts.app')

@section('title', 'Editar Categoría')

@section('content')
    <x-page-header title="Editar Categoría" subtitle="Modifica los datos de la categoría seleccionada" />

    <x-card class="max-w-xl">
        <form action="{{ route('categorias.update', $categoria) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            @include('categorias.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('categorias.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
