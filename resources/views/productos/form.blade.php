<flux:input
    name="descripcion"
    label="Descripción"
    placeholder="Ej: Gaseosa 1.5 Litros"
    value="{{ old('descripcion', $producto->descripcion ?? '') }}"
    required
    autofocus
/>

<flux:input
    name="precio"
    type="number"
    step="0.01"
    min="0"
    label="Precio"
    placeholder="Ej: 6500"
    value="{{ old('precio', $producto->precio ?? '') }}"
    required
/>

<flux:select name="categoria_id" label="Categoría" required>
    <flux:select.option value="" disabled :selected="old('categoria_id', $producto->categoria_id ?? '') === ''">Selecciona una categoría</flux:select.option>
    @foreach ($categorias as $categoria)
        <flux:select.option value="{{ $categoria->id }}" :selected="(string) old('categoria_id', $producto->categoria_id ?? '') === (string) $categoria->id">
            {{ $categoria->descripcion }}
        </flux:select.option>
    @endforeach
</flux:select>

<flux:select name="proveedor_id" label="Proveedor" required>
    <flux:select.option value="" disabled :selected="old('proveedor_id', $producto->proveedor_id ?? '') === ''">Selecciona un proveedor</flux:select.option>
    @foreach ($proveedores as $proveedor)
        <flux:select.option value="{{ $proveedor->id }}" :selected="(string) old('proveedor_id', $producto->proveedor_id ?? '') === (string) $proveedor->id">
            {{ $proveedor->nombre }}
        </flux:select.option>
    @endforeach
</flux:select>
