@extends('layouts.app')

@section('title', 'Detalle del Usuario')

@section('content')
    <x-page-header title="Detalle del Usuario" subtitle="Datos de acceso y facturas atendidas">
        <x-button :href="route('usuarios.edit', $usuario->id)" variant="warning">Editar</x-button>
        <x-button :href="route('usuarios.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $usuario->id }}</x-detail>
            <x-detail label="Nombre completo">{{ $usuario->nombre_completo }}</x-detail>
            <x-detail label="Correo electrónico">
                <span class="[overflow-wrap:anywhere]">{{ $usuario->correo }}</span>
            </x-detail>
            <x-detail label="Rol"><x-badge color="blue">{{ $usuario->rol }}</x-badge></x-detail>
            <x-detail label="Estado">
                <x-badge :color="$usuario->estado === 'Activo' ? 'green' : 'red'">{{ $usuario->estado }}</x-badge>
            </x-detail>
            <x-detail label="Registrado">{{ $usuario->created_at?->format('d/m/Y H:i') ?? '—' }}</x-detail>
            <x-detail label="Facturas atendidas">{{ $usuario->facturas->count() }}</x-detail>
        </dl>
    </x-card>

    <h2 class="mb-3 mt-8 text-lg font-semibold text-slate-900">Facturas atendidas</h2>

    <x-table :headers="['ID', 'Fecha', 'Cliente']">
        @forelse ($usuario->facturas as $factura)
            <tr>
                <td>
                    <a href="{{ route('facturas.show', $factura->id) }}" class="text-blue-600 hover:underline">{{ $factura->id }}</a>
                </td>
                <td class="whitespace-nowrap">{{ $factura->fecha->format('d/m/Y') }}</td>
                <td class="text-right">{{ $factura->cliente?->nombre ?? '—' }}</td>
            </tr>
        @empty
            <x-table.empty :colspan="3">Este usuario todavía no ha atendido facturas.</x-table.empty>
        @endforelse
    </x-table>
@endsection
