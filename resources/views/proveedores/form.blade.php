<flux:input
    name="nombre"
    label="Nombre"
    placeholder="Ej: Distribuidora La Economía"
    value="{{ old('nombre', $proveedor->nombre ?? '') }}"
    required
    autofocus
/>

<flux:input
    name="direccion"
    label="Dirección"
    placeholder="Ej: Sincelejo"
    value="{{ old('direccion', $proveedor->direccion ?? '') }}"
/>

<flux:input
    name="telefono"
    label="Teléfono"
    placeholder="Ej: 3001234567"
    value="{{ old('telefono', $proveedor->telefono ?? '') }}"
/>
