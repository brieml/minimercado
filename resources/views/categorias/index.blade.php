@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
    <x-page-header title="Listado de Categorías" subtitle="Clasificación de los productos del minimercado">
        <x-button :href="route('categorias.create')">
            <flux:icon.plus class="size-4" />
            Nueva Categoría
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Descripción', 'Acciones']">
        @forelse ($categorias as $categoria)
            <tr>
                <td>{{ $categoria->id }}</td>
                <td class="font-medium text-slate-900">{{ $categoria->descripcion }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('categorias.edit', $categoria->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="3">No hay categorías registradas.</x-table.empty>
        @endforelse
    </x-table>
@endsection
