@props(['href' => null])

@php
$classes = 'inline-flex items-center justify-center px-6 py-3 bg-surface-light border border-border-light rounded-xl font-semibold text-sm text-text-primary-light shadow-glass-sm hover:shadow-glass-md hover:bg-secondary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-25 transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
