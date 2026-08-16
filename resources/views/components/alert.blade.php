@props(['type', 'message'])

@php
    $classes = match ($type) {
        'success' => 'bg-success-50 text-success-800 border-success-200 dark:bg-success-900/30 dark:text-success-400 dark:border-success-800',
        'danger', 'error' => 'bg-danger-50 text-danger-800 border-danger-200 dark:bg-danger-900/30 dark:text-danger-400 dark:border-danger-800',
        'warning' => 'bg-warning-50 text-warning-800 border-warning-200 dark:bg-warning-900/30 dark:text-warning-400 dark:border-warning-800',
        'info' => 'bg-info-50 text-info-800 border-info-200 dark:bg-info-900/30 dark:text-info-400 dark:border-info-800',
        default => 'bg-secondary-50 text-secondary-800 border-secondary-200 dark:bg-secondary-900/30 dark:text-secondary-400 dark:border-secondary-800',
    };

    $icon = match ($type) {
        'success' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />',
        'danger', 'error' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />',
        'warning' => '<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.515 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625l6.28-10.875zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />',
        'info', 'default' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />',
    };
@endphp

<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, 5000)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    role="alert"
    class="mb-6 rounded-2xl p-4 border {{ $classes }} shadow-glass-sm"
>
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                {!! $icon !!}
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-semibold">{{ $message }}</p>
        </div>
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button
                    @click="show = false"
                    type="button"
                    class="inline-flex rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors {{ $type == 'success' ? 'hover:bg-success-100 dark:hover:bg-success-800' : ($type == 'danger' || $type == 'error' ? 'hover:bg-danger-100 dark:hover:bg-danger-800' : ($type == 'warning' ? 'hover:bg-warning-100 dark:hover:bg-warning-800' : 'hover:bg-info-100 dark:hover:bg-info-800')) }}"
                >
                    <span class="sr-only">Dismiss</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
