@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

@php
    $tieneError = $errors->has($name);
    $valor = $type === 'password' ? '' : old($name, $value);
@endphp

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($attributes->get('required'))
            <span class="text-red-600" aria-hidden="true">*</span>
        @endif
    </label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $valor }}"
        @if ($tieneError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2',
            'border-slate-300 focus:border-blue-600 focus:ring-blue-600/30' => ! $tieneError,
            'border-red-500 focus:border-red-600 focus:ring-red-600/20' => $tieneError,
        ]) }}>

    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror

    @if ($help)
        <p class="mt-1 text-xs text-slate-500">{{ $help }}</p>
    @endif
</div>
