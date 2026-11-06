@extends('layouts.app')

@section('title', 'Productos')

@section('content')
    <x-page-header title="Listado de Productos" subtitle="Artículos disponibles en el minimercado">
        <x-button :href="route('productos.create')">
            <flux:icon.plus class="size-4" />
            Nuevo Producto
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Descripción', 'Precio', 'Categoría', 'Proveedor', 'Acciones']">
        @forelse ($productos as $producto)
            <tr>
                <td>{{ $producto->id }}</td>
                <td class="font-medium text-slate-900">{{ $producto->descripcion }}</td>
                <td class="whitespace-nowrap">$ {{ number_format($producto->precio, 2, ',', '.') }}</td>
                <td>{{ $producto->categoria?->descripcion ?? '—' }}</td>
                <td>{{ $producto->proveedor?->nombre ?? '—' }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('productos.edit', $producto->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('productos.destroy', $producto->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="6">No hay productos registrados.</x-table.empty>
        @endforelse
    </x-table>
@endsection
