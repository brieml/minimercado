@props(['module', 'href'])

{{-- Enlace a un registro de otro módulo: si el rol no tiene acceso a ese módulo se muestra solo el texto. --}}
@if (auth()->user()->puedeAccederA($module))
    <a href="{{ $href }}" {{ $attributes->class('text-blue-600 hover:underline') }}>{{ $slot }}</a>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
