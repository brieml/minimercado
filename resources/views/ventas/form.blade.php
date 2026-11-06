@php
    $opcionesFactura = $facturas->mapWithKeys(fn ($factura) => [
        $factura->id => '#'.$factura->id.' · '.$factura->fecha->format('d/m/Y').' · '.($factura->cliente?->nombre ?? 'Sin cliente'),
    ]);
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.select name="factura_id" label="Factura" :options="$opcionesFactura" :selected="$venta->factura_id ?? null" required />
    </div>

    <x-form.select name="producto_id" label="Producto" :options="$productos->pluck('descripcion', 'id')"
        :selected="$venta->producto_id ?? null" required />

    <x-form.input name="cantidad" label="Cantidad" type="number" step="0.001" min="0.001" :value="$venta->cantidad ?? null" required />
</div>
