@extends('layouts.app')
@section('title', 'Detail Stock Opname')
@section('content')
<div class="w-full">
    <a href="{{ route('stock-opname.index') }}" class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:underline mb-6">← Kembali ke Daftar Stock Opname</a>

    @if(session('success'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 text-sm text-danger-700 dark:text-danger-300">{{ session('error') }}</div>
    @endif

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark font-mono">{{ $stockOpname->opname_number }}</h2>
                <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">Tanggal: {{ $stockOpname->opname_date?->format('d/m/Y') }} · Dibuat oleh: {{ $stockOpname->created_by_name ?? $stockOpname->createdBy?->name ?? '-' }}</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center flex-wrap gap-3">
                @if($stockOpname->status === 'approved')
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">Disetujui</span>
                @else
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300">Draft</span>
                    <form method="POST" action="{{ route('stock-opname.approve', $stockOpname) }}" onsubmit="return confirm('Setujui stock opname dan sesuaikan stok?')">
                        @csrf
                        <x-primary-button type="submit">Setujui & Sesuaikan Stok</x-primary-button>
                    </form>
                    <form method="POST" action="{{ route('stock-opname.destroy', $stockOpname) }}" onsubmit="return confirm('Hapus stock opname ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-900/20">Hapus</button>
                    </form>
                @endif
            </div>
        </div>
        @if($stockOpname->notes)
        <p class="mt-4 text-sm text-text-secondary-light dark:text-text-secondary-dark border-t border-border-light dark:border-border-dark pt-4">Catatan: {{ $stockOpname->notes }}</p>
        @endif
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-4">Rincian Item</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-4 px-4 text-left font-semibold">Obat</th>
                        <th class="pb-4 px-4 text-right font-semibold">Stok Sistem</th>
                        <th class="pb-4 px-4 text-right font-semibold">Stok Aktual</th>
                        <th class="pb-4 px-4 text-right font-semibold">Selisih</th>
                        <th class="pb-4 px-4 text-left font-semibold">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockOpname->items as $item)
                    <tr class="border-b border-border-light dark:border-border-dark">
                        <td class="py-4 px-4 font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $item->medicine?->name }}</td>
                        <td class="py-4 px-4 text-right text-text-primary-light dark:text-text-primary-dark">{{ $item->system_quantity }}</td>
                        <td class="py-4 px-4 text-right text-text-primary-light dark:text-text-primary-dark">{{ $item->actual_quantity }}</td>
                        <td class="py-4 px-4 text-right font-mono font-semibold {{ $item->difference < 0 ? 'text-danger-600 dark:text-danger-400' : ($item->difference > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-text-secondary-light dark:text-text-secondary-dark') }}">
                            {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                        </td>
                        <td class="py-4 px-4 text-text-secondary-light dark:text-text-secondary-dark">{{ $item->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-end">
            <p class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Total Selisih: <span class="font-mono {{ $stockOpname->total_difference < 0 ? 'text-danger-600 dark:text-danger-400' : ($stockOpname->total_difference > 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}">{{ $stockOpname->total_difference > 0 ? '+' : '' }}{{ $stockOpname->total_difference }}</span></p>
        </div>
    </div>
</div>
@endsection