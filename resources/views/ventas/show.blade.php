@extends('layouts.app')

@section('title', 'Detalle de la Venta')

@section('content')
    <x-page-header title="Detalle de la Venta" subtitle="Producto vendido dentro de una factura">
        <x-button :href="route('ventas.edit', $venta->id)" variant="warning">Editar</x-button>
        <x-button :href="route('ventas.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $venta->id }}</x-detail>
            <x-detail label="Factura">
                @if ($venta->factura)
                    <a href="{{ route('facturas.show', $venta->factura->id) }}" class="text-blue-600 hover:underline">#{{ $venta->factura->id }} · {{ $venta->factura->fecha->format('d/m/Y') }}</a>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Cliente">
                @if ($venta->factura?->cliente)
                    <a href="{{ route('clientes.show', $venta->factura->cliente->id) }}" class="text-blue-600 hover:underline">{{ $venta->factura->cliente->nombre }}</a>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Producto">
                @if ($venta->producto)
                    <a href="{{ route('productos.show', $venta->producto->id) }}" class="text-blue-600 hover:underline">{{ $venta->producto->descripcion }}</a>
                @else
                    —
                @endif
            </x-detail>
            <x-detail label="Cantidad">{{ number_format($venta->cantidad, 3, ',', '.') }}</x-detail>
            <x-detail label="Precio unitario">$ {{ number_format($venta->producto?->precio ?? 0, 2, ',', '.') }}</x-detail>
            <x-detail label="Subtotal">
                <span class="font-semibold text-slate-900">$ {{ number_format($venta->subtotal, 2, ',', '.') }}</span>
            </x-detail>
            <x-detail label="Registrada">{{ $venta->created_at?->format('d/m/Y H:i') ?? '—' }}</x-detail>
        </dl>
    </x-card>
@endsection
