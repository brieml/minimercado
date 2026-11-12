@extends('layouts.app')

@section('title', 'Detalle del Producto')

@section('content')
    <x-page-header title="Detalle del Producto" subtitle="Información completa del producto seleccionado">
        <x-button :href="route('productos.edit', $producto->id)" variant="warning">Editar</x-button>
        <x-button :href="route('productos.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $producto->id }}</x-detail>
            <x-detail label="Descripción">{{ $producto->descripcion }}</x-detail>
            <x-detail label="Precio">$ {{ number_format($producto->precio, 2, ',', '.') }}</x-detail>
            <x-detail label="Categoría">
                @if ($producto->categoria)
                    <a href="{{ route('categorias.show', $producto->categoria->id) }}" class="text-blue-600 hover:underline">{{ $producto->categoria->descripcion }}</a>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Proveedor">
                @if ($producto->proveedor)
                    <a href="{{ route('proveedores.show', $producto->proveedor->id) }}" class="text-blue-600 hover:underline">{{ $producto->proveedor->nombre }}</a>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Ventas registradas">{{ $producto->ventas_count }}</x-detail>
            <x-detail label="Registrado">{{ $producto->created_at?->format('d/m/Y H:i') ?? '—' }}</x-detail>
        </dl>
    </x-card>
@endsection
