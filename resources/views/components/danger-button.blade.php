<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-danger-600 border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide hover:bg-danger-700 focus:bg-danger-700 active:bg-danger-800 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 shadow-glass-sm hover:shadow-glass-md transition-all duration-200 dark:bg-danger-500 dark:hover:bg-danger-600 dark:focus:ring-danger-400 dark:focus:ring-offset-background-dark']) }}>
    {{ $slot }}
</button>
