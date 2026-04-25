<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ZYLOS'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('head')
</head>

<body class="bg-slate-50 text-slate-900 antialiased">
    @hasSection('navbar')
    @yield('navbar')
    @else
    @include('layouts.partials.navbar')
    @endif

    <main>
        @yield('content')
    </main>

    @hasSection('footer')
    @yield('footer')
    @else
    @include('layouts.partials.footer')
    @endif

    @stack('scripts')
</body>

</html>