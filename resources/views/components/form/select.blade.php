@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => 'Seleccione una opción'])

@php
    $tieneError = $errors->has($name);
    $seleccionado = old($name, $selected);
@endphp

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($attributes->get('required'))
            <span class="text-red-600" aria-hidden="true">*</span>
        @endif
    </label>

    <select id="{{ $name }}" name="{{ $name }}"
        @if ($tieneError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2',
            'border-slate-300 focus:border-blue-600 focus:ring-blue-600/30' => ! $tieneError,
            'border-red-500 focus:border-red-600 focus:ring-red-600/20' => $tieneError,
        ]) }}>
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $seleccionado)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>
