@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        // Módulo => [etiqueta, icono, color], en el orden en que se muestran las tarjetas.
        $tarjetas = [
            'productos' => ['Productos', 'cube', 'blue'],
            'categorias' => ['Categorías', 'tag', 'amber'],
            'proveedores' => ['Proveedores', 'truck', 'rose'],
            'clientes' => ['Clientes', 'user-group', 'green'],
            'facturas' => ['Facturas', 'document-text', 'violet'],
            'ventas' => ['Ventas', 'shopping-cart', 'sky'],
            'usuarios' => ['Usuarios', 'users', 'slate'],
        ];
    @endphp

    <x-page-header title="Dashboard" :subtitle="'Bienvenido, '.auth()->user()->nombre_completo" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tarjetas as $modulo => [$etiqueta, $icono, $color])
            @isset($totales[$modulo])
                <x-stat-card :label="$etiqueta" :value="$totales[$modulo]" :href="route($modulo.'.index')" :icon="$icono" :color="$color" />
            @endisset
        @endforeach
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

            <dt class="text-slate-500">Módulos</dt>
            <dd class="text-slate-900">
                {{ collect($tarjetas)->only($totales->keys())->map(fn (array $tarjeta) => $tarjeta[0])->implode(', ') }}
            </dd>
        </dl>
    </x-card>
@endsection
