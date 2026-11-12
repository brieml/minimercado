@php
    // Cantidad sin ceros sobrantes (2 en lugar de 2.000) para el campo numérico.
    $cantidadActual = rtrim(rtrim(number_format((float) $venta->cantidad, 3, '.', ''), '0'), '.');

    $linea = [
        'producto_id' => old('producto_id', $venta->producto_id),
        'cantidad' => old('cantidad', $cantidadActual),
    ];
@endphp

<x-form.errors />

<dl class="mb-5 grid gap-x-8 gap-y-4 sm:grid-cols-2">
    <x-detail label="Factura">
        @if ($venta->factura)
            #{{ $venta->factura->id }} · {{ $venta->factura->fecha->format('d/m/Y') }}
        @else
            —
        @endif
    </x-detail>
    <x-detail label="Cliente">{{ $venta->factura?->cliente?->nombre ?? '—' }}</x-detail>
</dl>

@include('ventas.linea')
