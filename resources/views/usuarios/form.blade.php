@php $esEdicion = isset($usuario); @endphp

<div class="grid grid-cols-2 gap-4">
    <flux:input
        name="nombre"
        label="Nombre"
        value="{{ old('nombre', $usuario->nombre ?? '') }}"
        required
        autofocus
    />

    <flux:input
        name="apellido"
        label="Apellido"
        value="{{ old('apellido', $usuario->apellido ?? '') }}"
        required
    />
</div>

<flux:input
    name="correo"
    type="email"
    label="Correo electrónico"
    placeholder="ejemplo@minimercado.com"
    value="{{ old('correo', $usuario->correo ?? '') }}"
    required
/>

<flux:input
    name="password"
    type="password"
    :label="$esEdicion ? 'Nueva contraseña' : 'Contraseña'"
    :description="$esEdicion ? 'Déjala en blanco para mantener la actual.' : null"
    placeholder="Mínimo 8 caracteres"
    :required="! $esEdicion"
/>

<div class="grid grid-cols-2 gap-4">
    <flux:select name="rol" label="Rol" required>
        @foreach (['Administrador', 'Cajero', 'Inventario'] as $rol)
            <flux:select.option value="{{ $rol }}" :selected="old('rol', $usuario->rol ?? 'Cajero') === $rol">{{ $rol }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select name="estado" label="Estado" required>
        @foreach (['Activo', 'Inactivo'] as $estado)
            <flux:select.option value="{{ $estado }}" :selected="old('estado', $usuario->estado ?? 'Activo') === $estado">{{ $estado }}</flux:select.option>
        @endforeach
    </flux:select>
</div>
