@extends('layouts.app')

@section('title', 'Acceso denegado')

@section('content')
    <x-page-header title="Acceso denegado" subtitle="Tu rol no incluye este módulo" />

    <x-card class="max-w-xl">
        <div class="flex items-start gap-4">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                <flux:icon.lock-closed class="size-6" />
            </span>

            <div>
                <p class="text-sm font-medium text-slate-900">
                    {{ $exception->getMessage() ?: 'No tienes permisos para acceder a este módulo.' }}
                </p>

                <p class="mt-2 text-sm text-slate-500">
                    El rol <x-badge color="blue">{{ auth()->user()->rol }}</x-badge> no incluye este módulo.
                    En el menú lateral aparecen los módulos que sí puedes usar.
                </p>

                <x-button :href="route('dashboard')" class="mt-5">Ir al dashboard</x-button>
            </div>
        </div>
    </x-card>
@endsection
