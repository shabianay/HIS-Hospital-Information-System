@extends('layouts.app')
@section('title', 'Papan Jadwal Dokter')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <div>
            <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Papan Jadwal Dokter</h3>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">Ringkasan jadwal dokter per hari dalam satu minggu.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('schedules.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-border-light dark:border-border-dark rounded-xl text-sm font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Daftar Jadwal</a>
            <a href="{{ route('schedules.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">Tambah Jadwal</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-border-light dark:border-border-dark">
                    <th class="py-3 px-3 font-semibold text-text-secondary-light dark:text-text-secondary-dark sticky left-0 bg-surface-light dark:bg-surface-dark min-w-[160px]">Dokter</th>
                    @foreach($days as $day)
                        <th class="py-3 px-3 font-semibold text-text-secondary-light dark:text-text-secondary-dark capitalize min-w-[130px]">
                            {{ $day }}
                            @if(\Carbon\Carbon::now()->translatedFormat('l') === $day)
                                <span class="ml-1 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 px-2 py-0.5 text-[10px] uppercase">Hari ini</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $entry)
                    <tr class="border-b border-border-light dark:border-border-dark align-top">
                        <td class="py-3 px-3 font-semibold text-text-primary-light dark:text-text-primary-dark sticky left-0 bg-surface-light dark:bg-surface-dark">
                            {{ $entry['doctor']?->name ?? '-' }}
                            <span class="block text-xs font-normal text-text-secondary-light dark:text-text-secondary-dark">{{ $entry['doctor']?->poli?->name ?? '' }}</span>
                        </td>
                        @foreach($days as $day)
                            <td class="py-3 px-3">
                                @if($entry['days']->has($day))
                                    @foreach($entry['days'][$day] as $slot)
                                        <div class="mb-2 rounded-lg border border-primary-200 dark:border-primary-800 bg-primary-50/60 dark:bg-primary-900/20 p-2">
                                            <p class="text-xs font-semibold text-primary-700 dark:text-primary-300">{{ $slot['poli'] }}</p>
                                            <p class="mt-0.5 text-xs text-text-primary-light dark:text-text-primary-dark">{{ $slot['start'] }} - {{ $slot['end'] }}</p>
                                            <p class="mt-0.5 text-[11px] text-text-secondary-light dark:text-text-secondary-dark">Kuota: {{ $slot['quota'] }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada jadwal dokter yang aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection