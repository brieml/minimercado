@extends('layouts.app')

@section('title', 'Facturas')

@section('content')
    <x-page-header title="Listado de Facturas" subtitle="Encabezados de las ventas realizadas">
        <x-button :href="route('facturas.create')">
            <flux:icon.plus class="size-4" />
            Nueva Factura
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Fecha', 'Cliente', 'Atendió', 'Acciones']">
        @forelse ($facturas as $factura)
            <tr>
                <td>{{ $factura->id }}</td>
                <td class="whitespace-nowrap font-medium text-slate-900">{{ $factura->fecha->format('d/m/Y') }}</td>
                <td>{{ $factura->cliente?->nombre ?? '—' }}</td>
                <td>{{ $factura->usuario?->nombre_completo ?? '—' }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('facturas.edit', $factura->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('facturas.destroy', $factura->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="5">No hay facturas registradas.</x-table.empty>
        @endforelse
    </x-table>
@endsection
