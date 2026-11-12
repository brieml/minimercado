@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <x-page-header title="Listado de Clientes" subtitle="Personas a las que se les vende en el minimercado">
        <x-button :href="route('clientes.create')">
            <flux:icon.plus class="size-4" />
            Nuevo Cliente
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Nombre', 'Dirección', 'Teléfono', 'Acciones']">
        @forelse ($clientes as $cliente)
            <tr>
                <td>{{ $cliente->id }}</td>
                <td class="font-medium text-slate-900">{{ $cliente->nombre }}</td>
                <td>{{ $cliente->direccion ?? '—' }}</td>
                <td>{{ $cliente->telefono ?? '—' }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('clientes.show', $cliente->id)" variant="secondary" size="sm">
                            Ver
                        </x-button>
                        <x-button :href="route('clientes.edit', $cliente->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="5">No hay clientes registrados.</x-table.empty>
        @endforelse
    </x-table>
@endsection
