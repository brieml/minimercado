<flux:input
    name="nombre"
    label="Nombre"
    placeholder="Ej: Juan Pérez"
    value="{{ old('nombre', $cliente->nombre ?? '') }}"
    required
    autofocus
/>

<flux:input
    name="direccion"
    label="Dirección"
    placeholder="Ej: Barrio Centro"
    value="{{ old('direccion', $cliente->direccion ?? '') }}"
/>

<flux:input
    name="telefono"
    label="Teléfono"
    placeholder="Ej: 3001234567"
    value="{{ old('telefono', $cliente->telefono ?? '') }}"
/>
