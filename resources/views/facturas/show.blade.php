@extends('layouts.app')

@section('title', 'Detalle de la Factura')

@section('content')
    <x-page-header :title="'Factura #'.$factura->id" subtitle="Encabezado, productos vendidos y total">
        <x-button :href="route('facturas.edit', $factura->id)" variant="warning">Editar</x-button>
        <x-button :href="route('facturas.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $factura->id }}</x-detail>
            <x-detail label="Fecha">{{ $factura->fecha->format('d/m/Y') }}</x-detail>
            <x-detail label="Cliente">
                @if ($factura->cliente)
                    <x-module-link module="clientes" :href="route('clientes.show', $factura->cliente->id)">{{ $factura->cliente->nombre }}</x-module-link>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Atendido por">
                @if ($factura->usuario)
                    <x-module-link module="usuarios" :href="route('usuarios.show', $factura->usuario->id)">{{ $factura->usuario->nombre_completo }}</x-module-link>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Productos vendidos">{{ $factura->ventas->count() }}</x-detail>
            <x-detail label="Total">
                <span class="font-semibold text-slate-900">$ {{ number_format($factura->total, 2, ',', '.') }}</span>
            </x-detail>
        </dl>
    </x-card>

    <h2 class="mb-3 mt-8 text-lg font-semibold text-slate-900">Productos de la factura</h2>

    <x-table :headers="['Producto', 'Cantidad', 'Precio unitario', 'Subtotal']">
        @forelse ($factura->ventas as $venta)
            <tr>
                <td class="font-medium text-slate-900">
                    @if ($venta->producto)
                        <x-module-link module="productos" :href="route('productos.show', $venta->producto->id)">{{ $venta->producto->descripcion }}</x-module-link>
                    @else
                        —
                    @endif
                </td>
                <td class="whitespace-nowrap">{{ $venta->cantidad_formateada }}</td>
                <td class="whitespace-nowrap">$ {{ number_format($venta->producto?->precio ?? 0, 2, ',', '.') }}</td>
                <td class="whitespace-nowrap text-right">$ {{ number_format($venta->subtotal, 2, ',', '.') }}</td>
            </tr>
        @empty
            <x-table.empty :colspan="4">Esta factura todavía no tiene productos.</x-table.empty>
        @endforelse

        @if ($factura->ventas->isNotEmpty())
            <tr class="bg-slate-50">
                <td colspan="3" class="text-right font-semibold text-slate-900">Total</td>
                <td class="whitespace-nowrap text-right font-semibold text-slate-900">$ {{ number_format($factura->total, 2, ',', '.') }}</td>
            </tr>
        @endif
    </x-table>
@endsection
