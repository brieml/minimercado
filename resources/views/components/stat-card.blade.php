@props(['label', 'value', 'href', 'icon', 'color' => 'blue'])

@php
    $colors = [
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<a href="{{ $href }}"
    class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">
    <span class="flex size-12 shrink-0 items-center justify-center rounded-lg {{ $colors[$color] ?? $colors['blue'] }}">
        <flux:icon :name="$icon" class="size-6" />
    </span>

    <div class="min-w-0">
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
    </div>
</a>
