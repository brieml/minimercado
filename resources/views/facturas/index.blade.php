@extends('layouts.app')

@section('title', 'Facturas')

@section('content')
    <x-page-header title="Listado de Facturas" subtitle="Se generan automáticamente al registrar una venta" />

    <x-table :headers="['ID', 'Fecha', 'Cliente', 'Atendió', 'Total', 'Acciones']">
        @forelse ($facturas as $factura)
            <tr>
                <td>{{ $factura->id }}</td>
                <td class="whitespace-nowrap font-medium text-slate-900">{{ $factura->fecha->format('d/m/Y') }}</td>
                <td>{{ $factura->cliente?->nombre ?? '—' }}</td>
                <td>{{ $factura->usuario?->nombre_completo ?? '—' }}</td>
                <td class="whitespace-nowrap">$ {{ number_format($factura->total, 2, ',', '.') }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('facturas.show', $factura->id)" variant="secondary" size="sm">
                            Ver
                        </x-button>
                        <x-button :href="route('facturas.edit', $factura->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('facturas.destroy', $factura->id) }}" method="POST"
                            onsubmit="return confirm('Se eliminará la factura junto con los productos vendidos en ella. ¿Deseas continuar?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="6">No hay facturas registradas.</x-table.empty>
        @endforelse
    </x-table>
@endsection
