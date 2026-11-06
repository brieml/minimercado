<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Panel') | Minimercado</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full font-sans text-slate-800 antialiased">
    @include('partials.sidebar')

    <div class="lg:pl-64">
        @include('partials.topbar')

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>
</body>

</html>
