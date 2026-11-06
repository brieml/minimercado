@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        'bg-blue-600 text-white shadow-sm' => $active,
        'text-slate-300 hover:bg-white/10 hover:text-white' => ! $active,
    ]) }}>
    <flux:icon :name="$icon" class="size-5 shrink-0" />
    <span>{{ $slot }}</span>
</a>
