<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: window.__darkMode ?? false }" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script>
            (function () {
                try {
                    var t = localStorage.getItem('healthpro-theme');
                    if (t === 'dark') { document.documentElement.classList.add('dark'); }
                    window.__darkMode = (t === 'dark');
                } catch (e) {
                    window.__darkMode = false;
                }
            })();
        </script>

        <title>@yield('title', config('app.name', 'HealthPro'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text-primary-light antialiased bg-background-light dark:bg-background-dark dark:text-text-primary-dark">
        @yield('content')
    </body>
</html>