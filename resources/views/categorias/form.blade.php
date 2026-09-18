<flux:input
    name="descripcion"
    label="Descripción"
    placeholder="Ej: Bebidas"
    value="{{ old('descripcion', $categoria->descripcion ?? '') }}"
    required
    autofocus
/>
