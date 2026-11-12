@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
    <x-page-header title="Listado de Proveedores" subtitle="Empresas que abastecen el minimercado">
        <x-button :href="route('proveedores.create')">
            <flux:icon.plus class="size-4" />
            Nuevo Proveedor
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Nombre', 'Dirección', 'Teléfono', 'Acciones']">
        @forelse ($proveedores as $proveedor)
            <tr>
                <td>{{ $proveedor->id }}</td>
                <td class="font-medium text-slate-900">{{ $proveedor->nombre }}</td>
                <td>{{ $proveedor->direccion ?? '—' }}</td>
                <td>{{ $proveedor->telefono ?? '—' }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('proveedores.show', $proveedor->id)" variant="secondary" size="sm">
                            Ver
                        </x-button>
                        <x-button :href="route('proveedores.edit', $proveedor->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('proveedores.destroy', $proveedor->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="5">No hay proveedores registrados.</x-table.empty>
        @endforelse
    </x-table>
@endsection
