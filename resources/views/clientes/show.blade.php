@extends('layouts.app')

@section('title', 'Detalle del Cliente')

@section('content')
    <x-page-header title="Detalle del Cliente" subtitle="Datos del cliente y facturas asociadas">
        <x-button :href="route('clientes.edit', $cliente->id)" variant="warning">Editar</x-button>
        <x-button :href="route('clientes.index')" variant="secondary">Volver al listado</x-button>
    </x-page-header>

    <x-card class="max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <x-detail label="ID">{{ $cliente->id }}</x-detail>
            <x-detail label="Nombre">{{ $cliente->nombre }}</x-detail>
            <x-detail label="Dirección">{{ $cliente->direccion ?? '—' }}</x-detail>
            <x-detail label="Teléfono">{{ $cliente->telefono ?? '—' }}</x-detail>
            <x-detail label="Registrado">{{ $cliente->created_at?->format('d/m/Y H:i') ?? '—' }}</x-detail>
            <x-detail label="Facturas">{{ $cliente->facturas->count() }}</x-detail>
        </dl>
    </x-card>

    <h2 class="mb-3 mt-8 text-lg font-semibold text-slate-900">Facturas del cliente</h2>

    <x-table :headers="['ID', 'Fecha', 'Atendió']">
        @forelse ($cliente->facturas as $factura)
            <tr>
                <td>
                    <a href="{{ route('facturas.show', $factura->id) }}" class="text-blue-600 hover:underline">{{ $factura->id }}</a>
                </td>
                <td class="whitespace-nowrap">{{ $factura->fecha->format('d/m/Y') }}</td>
                <td class="text-right">{{ $factura->usuario?->nombre_completo ?? '—' }}</td>
            </tr>
        @empty
            <x-table.empty :colspan="3">Este cliente todavía no tiene facturas.</x-table.empty>
        @endforelse
    </x-table>
@endsection
