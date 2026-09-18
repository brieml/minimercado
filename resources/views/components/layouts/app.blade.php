<x-layouts.app.sidebar>
    <flux:main>
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" class="mb-6" />
        @endif

        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
