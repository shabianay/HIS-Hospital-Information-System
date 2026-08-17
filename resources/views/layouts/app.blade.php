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

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>[x-cloak]{display:none!important}</style>
    </head>
    <body class="font-sans antialiased bg-background-light text-text-primary-light dark:bg-background-dark dark:text-text-primary-dark" x-data="{ sidebarOpen: true, mobileMenu: false }">
        <div class="min-h-screen flex">
            {{-- Mobile backdrop --}}
            <div x-show="mobileMenu" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden" @click="mobileMenu = false"></div>

            @include('components.sidebar')

            <div class="flex-1 flex flex-col transition-all duration-300"
                 :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-0'">

                {{-- Topbar --}}
                <header class="sticky top-0 z-40 flex h-16 items-center justify-between bg-surface-light/80 dark:bg-surface-dark/80 backdrop-blur-md shadow-glass-md px-6 border-b border-border-light dark:border-border-dark">
                    <div class="flex items-center gap-4">
                        <button type="button" class="lg:hidden p-2 rounded-lg text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary-50 dark:hover:bg-primary-900 hover:text-primary-500 dark:hover:text-primary-400 transition"
                                @click="mobileMenu = !mobileMenu">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        </button>
                        <button type="button" class="hidden lg:block p-2 rounded-lg text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary-50 dark:hover:bg-primary-900 hover:text-primary-500 dark:hover:text-primary-400 transition"
                                @click="sidebarOpen = !sidebarOpen">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        </button>
                        <span class="text-xl font-semibold text-text-primary-light dark:text-text-primary-dark">@yield('title')</span>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Notifications Bell --}}
                        <div x-data="unreadNotifier()" x-init="init()">
                            <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-lg text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary-50 dark:hover:bg-primary-900 hover:text-primary-500 dark:hover:text-primary-400 transition">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m7.714 0a24.255 24.255 0 0 1-7.714 0m7.714 0a3 3 0 1 1-7.714 0"/></svg>
                                <span x-show="count > 0" x-cloak class="absolute top-0 right-0 inline-flex h-5 w-5 items-center justify-center rounded-full bg-danger-600 text-[10px] font-bold text-white" x-text="Math.min(count, 99)"></span>
                            </a>
                        </div>
                        <span class="hidden md:block text-text-secondary-light dark:text-text-secondary-dark">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-primary-600 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-primary-800 dark:text-primary-200 dark:hover:bg-primary-700 dark:focus:ring-offset-background-dark transition-colors">
                                Keluar
                            </button>
                        </form>
                        {{-- Dark Mode Toggle --}}
                        <button @click="darkMode = !darkMode" class="p-2 rounded-lg text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary-50 dark:hover:bg-primary-900 hover:text-primary-500 dark:hover:text-primary-400 transition">
                            <svg x-show="!darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.325 3.325l-.707.707M6.318 6.318l-.707-.707m12.728 0l-.707.707M6.318 17.682l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg x-show="darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                    </div>
                </header>

                <main class="flex-1 p-6 lg:p-8">
                    @if (session('success'))
                        @component('components.alert', ['type' => 'success', 'message' => session('success')])
                        @endcomponent
                    @endif

                    @if (session('error'))
                        @component('components.alert', ['type' => 'danger', 'message' => session('error')])
                        @endcomponent
                    @endif

                    @if (session('info'))
                        @component('components.alert', ['type' => 'info', 'message' => session('info')])
                        @endcomponent
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
        <script>
            function unreadNotifier() {
                return {
                    count: 0,
                    init() {
                        this.refresh();
                        setInterval(() => this.refresh(), 30000);
                    },
                    refresh() {
                        fetch('{{ route('notifications.unread-count') }}')
                            .then(r => r.json())
                            .then(d => { this.count = d.count || 0; })
                            .catch(() => {});
                    },
                };
            }
        </script>
        @stack('scripts')
    </body>
</html>