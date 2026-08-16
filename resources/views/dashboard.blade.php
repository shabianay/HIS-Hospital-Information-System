@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Statistic Cards Example --}}
        <div class="p-6 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-2xl shadow-glass-sm">
            <h3 class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium">Total Pasien</h3>
            <p class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark mt-2">1,284</p>
        </div>
        <div class="p-6 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-2xl shadow-glass-sm">
            <h3 class="text-text-secondary-light dark:text-text-secondary-dark text-sm font-medium">Janji Temu Hari Ini</h3>
            <p class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark mt-2">42</p>
        </div>
    </div>

    <div class="mt-8 p-8 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-2xl shadow-glass-sm text-text-primary-light dark:text-text-primary-dark">
        <h2 class="text-xl font-semibold">{{ __("Selamat Datang!") }}</h2>
        <p class="mt-2 text-text-secondary-light dark:text-text-secondary-dark">{{ __("Anda telah masuk ke sistem.") }}</p>
    </div>
@endsection
