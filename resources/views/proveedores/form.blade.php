<x-form.errors />

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.input name="nombre" label="Nombre" :value="$proveedor->nombre ?? null" maxlength="120" required />
    </div>

    <x-form.input name="direccion" label="Dirección" :value="$proveedor->direccion ?? null" maxlength="200" />

    <x-form.input name="telefono" label="Teléfono" type="tel" :value="$proveedor->telefono ?? null" maxlength="30" />
</div>
