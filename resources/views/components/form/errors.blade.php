@if ($errors->any())
    <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-semibold">Corrige los siguientes errores antes de continuar:</p>

        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $mensaje)
                <li>{{ $mensaje }}</li>
            @endforeach
        </ul>
    </div>
@endif
