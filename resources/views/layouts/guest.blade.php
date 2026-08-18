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
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/" class="flex items-center justify-center gap-2 mb-4">
                    <x-application-logo class="w-12 h-12 fill-current text-primary-500 dark:text-primary-400" />
                    <span class="text-2xl font-semibold text-text-primary-light dark:text-text-primary-dark">Health<span class="text-primary-600 dark:text-primary-400">Pro</span></span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-surface-light dark:bg-surface-dark shadow-glass-lg overflow-hidden rounded-2xl border border-border-light dark:border-border-dark">
                {{ $slot }}
            </div>

            {{-- Dark Mode Toggle --}}
            <div class="fixed bottom-6 right-6">
                <button @click="darkMode = !darkMode" class="p-3 rounded-full bg-surface-light dark:bg-surface-dark shadow-glass-md text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary-50 dark:hover:bg-primary-900 hover:text-primary-500 dark:hover:text-primary-400 transition-colors duration-200">
                    <svg x-show="!darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.325 3.325l-.707.707M6.318 6.318l-.707-.707m12.728 0l-.707.707M6.318 17.682l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg x-show="darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
            </div>
        </div>
    </body>
</html>
