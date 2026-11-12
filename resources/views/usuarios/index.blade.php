@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <x-page-header title="Listado de Usuarios" subtitle="Personas con acceso al sistema">
        <x-button :href="route('usuarios.create')">
            <flux:icon.plus class="size-4" />
            Nuevo Usuario
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Nombre', 'Correo', 'Rol', 'Estado', 'Acciones']">
        @forelse ($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->id }}</td>
                <td class="font-medium text-slate-900">{{ $usuario->nombre_completo }}</td>
                <td class="[overflow-wrap:anywhere]">{{ $usuario->correo }}</td>
                <td><x-badge color="blue">{{ $usuario->rol }}</x-badge></td>
                <td>
                    <x-badge :color="$usuario->estado === 'Activo' ? 'green' : 'red'">{{ $usuario->estado }}</x-badge>
                </td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('usuarios.show', $usuario->id)" variant="secondary" size="sm">
                            Ver
                        </x-button>
                        <x-button :href="route('usuarios.edit', $usuario->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="6">No hay usuarios registrados.</x-table.empty>
        @endforelse
    </x-table>
@endsection
