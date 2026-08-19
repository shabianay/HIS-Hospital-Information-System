@extends('layouts.app')
@section('title', 'Operasi ' . $surgery->surgery_number)
@section('content')
<div class="space-y-6">
    @php
        $badges = [
            'scheduled' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800',
            'in_progress' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
            'completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
            'cancelled' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
        ];
    @endphp

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Operasi {{ $surgery->surgery_number }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badges[$surgery->status] ?? '' }}">{{ \App\Models\Surgery::STATUSES[$surgery->status] ?? $surgery->status }}</span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ \App\Models\Surgery::TYPES[$surgery->surgery_type] ?? $surgery->surgery_type }}</span>
        </div>
        <a href="{{ route('surgeries.index') }}" class="inline-flex items-center rounded-xl border border-border-light dark:border-border-dark px-5 py-2.5 text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-all duration-200">← Kembali</a>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Informasi Operasi</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-sm">
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Pasien:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $surgery->patient?->name }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Operator:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $surgery->doctor?->name ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Kamar OK:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $surgery->operating_room ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Jadwal:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $surgery->scheduled_at?->format('d/m/Y H:i') }}</strong></div>
        </div>
        <div class="mt-4 rounded-xl bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800 px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Prosedur:</strong> {{ $surgery->procedure_name }}
            @if($surgery->icd9Procedure)
            <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">{{ $surgery->icd9Procedure->code }}</span>
            @endif
        </div>
        @if($surgery->pre_notes)
        <div class="mt-3 rounded-xl bg-secondary-50 dark:bg-secondary-900/10 border border-border-light dark:border-border-dark px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Catatan Persiapan:</strong> {{ $surgery->pre_notes }}
        </div>
        @endif
        @if($surgery->post_notes)
        <div class="mt-3 rounded-xl bg-success-50 dark:bg-success-900/10 border border-success-200 dark:border-success-800 px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Catatan Pasca Operasi:</strong> {{ $surgery->post_notes }}
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Mulai</p>
            <p class="mt-1 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $surgery->started_at?->format('d/m/Y H:i') ?? '-' }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Selesai</p>
            <p class="mt-1 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $surgery->finished_at?->format('d/m/Y H:i') ?? '-' }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Dibuat oleh</p>
            <p class="mt-1 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $surgery->createdBy?->name ?? '-' }}</p>
        </div>
    </div>

    @if(in_array($surgery->status, ['scheduled', 'in_progress']))
    <form action="{{ route('surgeries.status', $surgery) }}" method="POST" class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        @csrf
        @method('PATCH')
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Perbarui Status</h3>
        <div class="grid grid-cols-1 gap-5">
            <div>
                <x-input-label value="Status *" />
                <select name="status" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    @foreach(\App\Models\Surgery::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ $surgery->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label value="Catatan Pasca Operasi" />
                <textarea name="post_notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ $surgery->post_notes }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <x-primary-button type="submit">Simpan Status</x-primary-button>
        </div>
    </form>
    @endif

    <div class="flex justify-end">
        <form method="POST" action="{{ route('surgeries.destroy', $surgery) }}" onsubmit="return confirm('Hapus jadwal operasi ini?')" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-5 py-2.5 border border-danger-200 dark:border-danger-800 text-danger-700 dark:text-danger-400 text-sm font-semibold rounded-xl hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-all duration-200">Hapus Jadwal</button>
        </form>
    </div>
</div>
@endsection