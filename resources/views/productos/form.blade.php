<x-form.errors />

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.input name="descripcion" label="Descripción" :value="$producto->descripcion ?? null" maxlength="150" required />
    </div>

    <x-form.input name="precio" label="Precio" type="number" step="0.01" min="0" :value="$producto->precio ?? null" required />

    <div class="sm:col-span-2">
        <x-form.checkbox name="tiene_gramaje" label="Producto con gramaje (se vende por peso)"
            help="Solo estos productos admiten decimales en la cantidad de una venta (por ejemplo 0.250). Los demás se venden por unidades enteras."
            :checked="$producto->tiene_gramaje ?? false" />
    </div>

    <x-form.select name="categoria_id" label="Categoría" :options="$categorias->pluck('descripcion', 'id')"
        :selected="$producto->categoria_id ?? null" required />

    <x-form.select name="proveedor_id" label="Proveedor" :options="$proveedores->pluck('nombre', 'id')"
        :selected="$producto->proveedor_id ?? null" required />
</div>
