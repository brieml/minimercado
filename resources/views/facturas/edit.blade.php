@extends('layouts.app')

@section('title', 'Editar Factura')

@section('content')
    <x-page-header :title="'Editar Factura #'.$factura->id" subtitle="Corrige la fecha, el cliente o quien atendió la venta" />

    <x-card class="max-w-3xl">
        <form action="{{ route('facturas.update', $factura) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            @include('facturas.form')

            <div class="mt-6 flex items-center gap-3">
                <x-button type="submit">Actualizar</x-button>
                <x-button :href="route('facturas.index')" variant="secondary">Cancelar</x-button>
            </div>
        </form>
    </x-card>
@endsection
