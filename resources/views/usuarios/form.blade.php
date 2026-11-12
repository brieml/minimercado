<x-form.errors />

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.input name="nombre" label="Nombre" :value="$usuario->nombre ?? null" maxlength="100" required />

    <x-form.input name="apellido" label="Apellido" :value="$usuario->apellido ?? null" maxlength="100" required />

    <div class="sm:col-span-2">
        <x-form.input name="correo" label="Correo electrónico" type="email" :value="$usuario->correo ?? null" maxlength="150" required />
    </div>

    <div class="sm:col-span-2">
        <x-form.input name="password" label="Contraseña" type="password" autocomplete="new-password"
            :required="! isset($usuario)"
            :help="isset($usuario) ? 'Déjala en blanco para conservar la contraseña actual.' : null" />
    </div>

    <x-form.select name="rol" label="Rol" :options="array_combine($roles, $roles)" :selected="$usuario->rol ?? 'Cajero'" required />

    <x-form.select name="estado" label="Estado" :options="array_combine($estados, $estados)" :selected="$usuario->estado ?? 'Activo'" required />
</div>
