<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-primary-600 border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-glass-sm hover:shadow-glass-md transition-all duration-200 dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark']) }}>
    {{ $slot }}
</button>
