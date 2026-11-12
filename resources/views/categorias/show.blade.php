@extends('layouts.app')

@section('title', 'Detalle de la Categoría')

@section('content')
    <x-page-header title="Detalle de la Categoría" subtitle="Información de la categoría y de los productos que clasifica">
        <x-button :href="route('categorias.edit', $categoria->id)" variant="warning">Editar</x-button>
        <x-button :href="route('categorias.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $categoria->id }}</x-detail>
            <x-detail label="Descripción">{{ $categoria->descripcion }}</x-detail>
            <x-detail label="Registrada">{{ $categoria->created_at?->format('d/m/Y H:i') ?? '—' }}</x-detail>
            <x-detail label="Productos">{{ $categoria->productos->count() }}</x-detail>
        </dl>
    </x-card>

    <h2 class="mb-3 mt-8 text-lg font-semibold text-slate-900">Productos de esta categoría</h2>

    <x-table :headers="['ID', 'Descripción', 'Precio']">
        @forelse ($categoria->productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td class="font-medium text-slate-900">
                    <a href="{{ route('productos.show', $producto->id) }}" class="text-blue-600 hover:underline">{{ $producto->descripcion }}</a>
                </td>
                <td class="whitespace-nowrap text-right">$ {{ number_format($producto->precio, 2, ',', '.') }}</td>
            </tr>
        @empty
            <x-table.empty :colspan="3">Esta categoría todavía no tiene productos.</x-table.empty>
        @endforelse
    </x-table>
@endsection
