<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: window.__darkMode ?? false }" :class="{ 'dark': darkMode }" x-init="$watch('darkMode', v => localStorage.setItem('healthpro-theme', v ? 'dark' : 'light'))">
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

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-text-primary-light antialiased bg-background-light dark:bg-background-dark dark:text-text-primary-dark">
        @if ($full)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col sm:justify-center items-center px-6 py-16 sm:py-24">
                <div class="w-full max-w-sm sm:max-w-md">
                    {{ $slot }}
                </div>
            </div>
        @endif

        {{-- Dark Mode Toggle --}}
        <div class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl px-3 py-2 shadow-glass-md">
            <svg class="h-4 w-4 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.325 3.325l-.707.707M6.318 6.318l-.707-.707m12.728 0l-.707.707M6.318 17.682l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <button @click="darkMode = !darkMode" class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 dark:bg-slate-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 dark:focus:ring-offset-background-dark">
                <span class="inline-block h-4 w-4 translate-x-1 transform rounded-full bg-white shadow transition-transform duration-200 dark:translate-x-6" :class="darkMode ? 'bg-primary-400' : ''"></span>
            </button>
            <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </div>
    </body>
</html>
