@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => 'Seleccione una opción'])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($attributes->get('required'))
            <span class="text-red-600" aria-hidden="true">*</span>
        @endif
    </label>

    <select id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->class('block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/30') }}>
        <option value="">{{ $placeholder }}</option>

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $selected)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
