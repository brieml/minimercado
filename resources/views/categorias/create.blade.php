@extends('layouts.app')

@section('title', 'Crear Categoría')

@section('content')
    <x-page-header title="Crear Categoría" subtitle="Registra una nueva categoría de productos" />

    <x-card class="max-w-xl">
        <form action="{{ route('categorias.store') }}" method="POST">
            @csrf

            @include('categorias.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Guardar</x-button>
                <x-button :href="route('categorias.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
