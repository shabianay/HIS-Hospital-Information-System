@extends('layouts.app')
@section('title', 'Kartu Stok Obat')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Kartu Stok Obat</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('medicines.stock') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Manajemen Stok</a>
            <a href="{{ route('medicines.mutations') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Riwayat Mutasi</a>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <form method="GET" action="{{ route('medicines.stock-card') }}" class="flex flex-col sm:flex-row gap-3">
            <select name="medicine_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                @foreach($medicines as $m)
                    <option value="{{ $m->id }}" {{ $medicineId == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
            <x-primary-button type="submit">Tampilkan</x-primary-button>
        </form>
    </div>

    @if($medicine)
        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
                <div>
                    <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">{{ $medicine->name }}</h3>
                    <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                        {{ $medicine->generic_name ?: '-' }} · {{ $medicine->unit }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Saldo Akhir</p>
                    <p class="text-3xl font-bold {{ $balance <= $medicine->minimum_stock ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                        {{ $balance }} {{ $medicine->unit }}
                    </p>
                    <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Stok minimum: {{ $medicine->minimum_stock }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                            <th class="pb-4 px-4 font-semibold">Waktu</th>
                            <th class="pb-4 px-4 font-semibold">Referensi</th>
                            <th class="pb-4 px-4 font-semibold">Catatan</th>
                            <th class="pb-4 px-4 text-right font-semibold">Masuk</th>
                            <th class="pb-4 px-4 text-right font-semibold">Keluar</th>
                            <th class="pb-4 px-4 text-right font-semibold">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr class="border-b border-border-light dark:border-border-dark last:border-0 hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                                <td class="py-4 px-4 text-text-secondary-light dark:text-text-secondary-dark">{{ $entry['created_at']?->format('d/m/Y H:i') }}</td>
                                <td class="py-4 px-4 font-mono text-text-primary-light dark:text-text-primary-dark">{{ $entry['reference'] ?: '-' }}</td>
                                <td class="py-4 px-4 text-text-primary-light dark:text-text-primary-dark">{{ $entry['notes'] ?: '-' }}</td>
                                <td class="py-4 px-4 text-right text-success-600 dark:text-success-400 font-mono">{{ $entry['in'] !== null ? '+' . $entry['in'] : '' }}</td>
                                <td class="py-4 px-4 text-right text-danger-600 dark:text-danger-400 font-mono">{{ $entry['out'] !== null ? '-' . $entry['out'] : '' }}</td>
                                <td class="py-4 px-4 text-right font-mono font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $entry['balance'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada mutasi untuk obat ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm text-center text-text-secondary-light dark:text-text-secondary-dark">
            Tidak ada obat terdaftar.
        </div>
    @endif
</div>
@endsection