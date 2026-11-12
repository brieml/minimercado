@props(['label'])

<div>
    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</dt>
    <dd class="mt-1 text-sm text-slate-900">{{ $slot }}</dd>
</div>
