@props(['name', 'label', 'checked' => false, 'help' => null])

@php
    $marcado = filter_var(old($name, $checked), FILTER_VALIDATE_BOOLEAN);
@endphp

<div>
    {{-- Sin este campo oculto, una casilla desmarcada no se envía y no habría forma de quitar la marca. --}}
    <input type="hidden" name="{{ $name }}" value="0">

    <label for="{{ $name }}" class="flex cursor-pointer items-start gap-3">
        <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1" @checked($marcado)
            {{ $attributes->class('mt-0.5 size-4 shrink-0 cursor-pointer rounded accent-blue-600') }}>

        <span>
            <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>

            @if ($help)
                <span class="mt-0.5 block text-xs text-slate-500">{{ $help }}</span>
            @endif
        </span>
    </label>

    @error($name)
        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
