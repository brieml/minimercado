@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Dashboard" :subtitle="'Bienvenido, '.auth()->user()->nombre_completo" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Productos" :value="$productos" :href="route('productos.index')" icon="cube" color="blue" />
        <x-stat-card label="Categorías" :value="$categorias" :href="route('categorias.index')" icon="tag" color="amber" />
        <x-stat-card label="Proveedores" :value="$proveedores" :href="route('proveedores.index')" icon="truck" color="rose" />
        <x-stat-card label="Clientes" :value="$clientes" :href="route('clientes.index')" icon="user-group" color="green" />
        <x-stat-card label="Facturas" :value="$facturas" :href="route('facturas.index')" icon="document-text" color="violet" />
        <x-stat-card label="Ventas" :value="$ventas" :href="route('ventas.index')" icon="shopping-cart" color="sky" />
        <x-stat-card label="Usuarios" :value="$usuarios" :href="route('usuarios.index')" icon="users" color="slate" />
    </div>

    <x-card class="mt-6 max-w-xl">
        <h2 class="text-base font-semibold text-slate-900">Tu sesión</h2>

        <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm">
            <dt class="text-slate-500">Nombre</dt>
            <dd class="text-slate-900">{{ auth()->user()->nombre_completo }}</dd>

            <dt class="text-slate-500">Correo</dt>
            <dd class="break-all text-slate-900">{{ auth()->user()->correo }}</dd>

            <dt class="text-slate-500">Rol</dt>
            <dd><x-badge color="blue">{{ auth()->user()->rol }}</x-badge></dd>
        </dl>
    </x-card>
@endsection
