@extends('layouts.app')

@section('title', 'Detalle del Proveedor')

@section('content')
    <x-page-header title="Detalle del Proveedor" subtitle="Datos del proveedor y productos que suministra">
        <x-button :href="route('proveedores.edit', $proveedor->id)" variant="warning">Editar</x-button>
        <x-button :href="route('proveedores.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $proveedor->id }}</x-detail>
            <x-detail label="Nombre">{{ $proveedor->nombre }}</x-detail>
            <x-detail label="Dirección">{{ $proveedor->direccion ?? '—' }}</x-detail>
            <x-detail label="Teléfono">{{ $proveedor->telefono ?? '—' }}</x-detail>
            <x-detail label="Registrado">{{ $proveedor->created_at?->format('d/m/Y H:i') ?? '—' }}</x-detail>
            <x-detail label="Productos">{{ $proveedor->productos->count() }}</x-detail>
        </dl>
    </x-card>

    <h2 class="mb-3 mt-8 text-lg font-semibold text-slate-900">Productos del proveedor</h2>

    <x-table :headers="['ID', 'Descripción', 'Categoría', 'Precio']">
        @forelse ($proveedor->productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td class="font-medium text-slate-900">
                    <a href="{{ route('productos.show', $producto->id) }}" class="text-blue-600 hover:underline">{{ $producto->descripcion }}</a>
                </td>
                <td>{{ $producto->categoria?->descripcion ?? '—' }}</td>
                <td class="whitespace-nowrap text-right">$ {{ number_format($producto->precio, 2, ',', '.') }}</td>
            </tr>
        @empty
            <x-table.empty :colspan="4">Este proveedor todavía no tiene productos.</x-table.empty>
        @endforelse
    </x-table>
@endsection
