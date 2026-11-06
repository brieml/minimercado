@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
    <x-page-header title="Listado de Ventas" subtitle="Productos vendidos en cada factura">
        <x-button :href="route('ventas.create')">
            <flux:icon.plus class="size-4" />
            Nueva Venta
        </x-button>
    </x-page-header>

    <x-table :headers="['ID', 'Factura', 'Cliente', 'Producto', 'Cantidad', 'Acciones']">
        @forelse ($ventas as $venta)
            <tr>
                <td>{{ $venta->id }}</td>
                <td class="whitespace-nowrap">
                    @if ($venta->factura)
                        #{{ $venta->factura->id }} · {{ $venta->factura->fecha->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ $venta->factura?->cliente?->nombre ?? '—' }}</td>
                <td class="font-medium text-slate-900">{{ $venta->producto?->descripcion ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ number_format($venta->cantidad, 3, ',', '.') }}</td>
                <td>
                    <div class="flex justify-end gap-2">
                        <x-button :href="route('ventas.edit', $venta->id)" variant="warning" size="sm">
                            Editar
                        </x-button>
                        <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger" size="sm">Eliminar</x-button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-table.empty :colspan="6">No hay ventas registradas.</x-table.empty>
        @endforelse
    </x-table>
@endsection
