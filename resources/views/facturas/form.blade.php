<x-form.errors />

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2 sm:max-w-xs">
        <x-form.input name="fecha" label="Fecha" type="date" :value="$factura->fecha->format('Y-m-d')" required />
    </div>

    <x-form.select name="cliente_id" label="Cliente" :options="$clientes->pluck('nombre', 'id')"
        :selected="$factura->cliente_id" required />

    <x-form.select name="usuario_id" label="Atendido por" :options="$usuarios->pluck('nombre_completo', 'id')"
        :selected="$factura->usuario_id" required />
</div>
