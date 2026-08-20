<x-guest-layout>
    <div class="text-center mb-10">
        <a href="/" class="inline-flex items-center gap-2.5">
            <x-application-logo class="w-9 h-9 fill-current text-primary-600 dark:text-primary-400" />
            <span class="text-xl font-semibold text-text-primary-light dark:text-text-primary-dark">Health<span
                    class="text-primary-600 dark:text-primary-400">Pro</span></span>
        </a>
    </div>

    <div
        class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-2xl px-8 py-9 shadow-glass-md">
        <h1 class="text-xl font-semibold tracking-tight text-text-primary-light dark:text-text-primary-dark">Masuk</h1>
        <p class="mt-1.5 text-sm text-text-secondary-light dark:text-text-secondary-dark">Silakan masuk untuk
            melanjutkan.</p>

        <div class="mt-8">
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email"
                        :value="old('email')" required autofocus autocomplete="username"
                        placeholder="nama@rumahsakit.co.id" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-text-input id="password" class="block mt-1.5 w-full" type="password" name="password"
                        placeholder="••••••••" required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                        class="rounded border-border-light text-primary-600 focus:ring-primary-500 focus:ring-offset-0 dark:border-border-dark dark:bg-surface-dark dark:focus:ring-primary-400"
                        name="remember">
                    <span
                        class="ms-2 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ __('Ingat saya') }}</span>
                </label>

                <div class="pt-2">
                    <x-primary-button class="w-full">
                        {{ __('Masuk') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
