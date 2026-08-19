@extends('layouts.app')
@section('title', 'Detail Kamar')
@section('content')
<div class="w-full space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $room->name }} <span class="text-sm font-medium text-text-secondary-light dark:text-text-secondary-dark">{{ $room->code }}</span></h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('beds.create', ['room_id' => $room->id]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">Tambah Tempat Tidur</a>
                <a href="{{ route('rooms.edit', $room) }}" class="inline-flex items-center justify-center px-4 py-2 bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 text-sm font-semibold text-warning-700 dark:text-warning-400 rounded-xl transition-all">Edit</a>
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-border-light dark:border-border-dark text-sm font-semibold text-text-primary-light dark:text-text-primary-dark rounded-xl transition-all">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tipe</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\Room::ROOM_TYPES[$room->room_type] ?? $room->room_type }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tarif per Hari</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($room->price_per_day, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Status</span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $room->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800' : 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800' }}">
                    {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>

        @if($room->description)
            <div class="mb-6">
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Deskripsi</span>
                <p class="text-sm text-text-primary-light dark:text-text-primary-dark">{{ $room->description }}</p>
            </div>
        @endif

        <div class="mt-6 border-t border-border-light dark:border-border-dark pt-6">
            <h4 class="mb-3 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Okupansi Kamar: {{ $occupiedBeds }} / {{ $totalBeds }} tempat tidur terisi</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($room->beds as $bed)
                    <div class="rounded-xl border border-border-light dark:border-border-dark p-4 {{ $bed->admissions_count > 0 ? 'bg-danger-50/50 dark:bg-danger-900/10' : 'bg-success-50/50 dark:bg-success-900/10' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">No. TT {{ $bed->bed_number }}</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $bed->admissions_count > 0 ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' }}">
                                {{ $bed->admissions_count > 0 ? 'Terisi' : 'Kosong' }}
                            </span>
                        </div>
                        @php $activeBedAdmission = $room->admissions->first(fn ($a) => $a->bed_id === $bed->id); @endphp
                        @if($activeBedAdmission)
                            <p class="mt-2 text-xs text-text-secondary-light dark:text-text-secondary-dark">{{ $activeBedAdmission->patient?->name }}</p>
                        @endif
                    </div>
                @empty
                    <p class="col-span-full text-sm text-text-secondary-light dark:text-text-secondary-dark">Belum ada tempat tidur di kamar ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection