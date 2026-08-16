@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-500 text-start text-base font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 focus:outline-none focus:text-primary-800 dark:focus:text-primary-300 focus:bg-primary-100 dark:focus:bg-primary-800/40 focus:border-primary-600 dark:focus:border-primary-500 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-text-secondary-light dark:text-text-secondary-dark hover:text-text-primary-light dark:hover:text-text-primary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800/50 hover:border-border-light dark:hover:border-border-dark focus:outline-none focus:text-text-primary-light dark:focus:text-text-primary-dark focus:bg-secondary-50 dark:focus:bg-secondary-800/50 focus:border-border-light dark:focus:border-border-dark transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
