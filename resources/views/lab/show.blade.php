@extends('layouts.app')
@section('title', 'Permintaan Lab #' . str_pad($labRequest->id, 4, '0', STR_PAD_LEFT))
@section('content')
<div class="space-y-6">
    @php
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
            'in_progress' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
            'completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
            'cancelled' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
        ];
        $labels = ['pending' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Permintaan Lab #{{ str_pad($labRequest->id, 4, '0', STR_PAD_LEFT) }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badges[$labRequest->status] }}">{{ $labels[$labRequest->status] }}</span>
            @if($labRequest->is_urgent)<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400">PRIORITAS</span>@endif
        </div>
        <a href="{{ route('lab.requests.pdf', $labRequest) }}" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-5 py-2.5 text-sm font-semibold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all duration-200">Cetak PDF</a>
        @can('create', App\Models\Billing::class)
            @if($labRequest->appointment && ! $labRequest->appointment->billing)
            <a href="{{ route('billings.create', $labRequest->appointment) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 text-sm font-semibold shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Buat Tagihan</a>
            @endif
        @endcan
        <a href="{{ route('lab.requests') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">← Kembali</a>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Informasi Pasien</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-sm">
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Pasien:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $labRequest->patient?->name }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Antrian:</span> <strong class="font-mono text-text-primary-light dark:text-text-primary-dark">{{ $labRequest->appointment?->queue_number ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Perujuk:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $labRequest->doctor?->name ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Dibuat:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $labRequest->created_at?->format('d/m/Y H:i') }}</strong></div>
        </div>
        @if($labRequest->notes)
        <div class="mt-4 rounded-xl bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800 px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Catatan:</strong> {{ $labRequest->notes }}
        </div>
        @endif
    </div>

    <form action="{{ route('lab.requests.process', $labRequest) }}" method="POST">
        @csrf
        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Hasil Pemeriksaan</h3>

            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-4 px-4 font-semibold">Pemeriksaan</th>
                        <th class="pb-4 px-4 font-semibold">Nilai Rujukan</th>
                        <th class="pb-4 px-4 font-semibold">Hasil</th>
                        <th class="pb-4 px-4 font-semibold">Status</th>
                        <th class="pb-4 px-4 font-semibold">Catatan</th>
                    </tr>
                </x-slot>
                @forelse($labRequest->items as $item)
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-4 px-4">
                        <div class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $item->test_name }}</div>
                        <div class="text-xs text-text-secondary-light dark:text-text-secondary-dark">{{ $item->unit ?? '' }}</div>
                    </td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $item->reference_range ?? '-' }}</td>
                    <td class="py-4 px-4">
                        <input type="text" name="items[{{ $item->id }}][result_value]" value="{{ $item->result_value }}"
                            class="w-full border border-border-light bg-surface-light px-3 py-2 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-lg dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"
                            placeholder="Isi hasil...">
                    </td>
                    <td class="py-4 px-4">
                        <select name="items[{{ $item->id }}][result_status]"
                            class="border border-border-light bg-surface-light px-3 py-2 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-lg dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                            @foreach(['pending' => 'Belum Diperiksa', 'normal' => 'Normal', 'abnormal' => 'Abnormal'] as $val => $label)
                                <option value="{{ $val }}" {{ $item->result_status == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="py-4 px-4">
                        <input type="text" name="items[{{ $item->id }}][result_notes]" value="{{ $item->result_notes }}"
                            class="w-full border border-border-light bg-surface-light px-3 py-2 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-lg dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"
                            placeholder="Catatan hasil...">
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada item pemeriksaan.</td></tr>
                @endforelse
            </x-table>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-input-label value="Status Permintaan:" />
                    <select name="status" class="border border-border-light bg-surface-light px-3 py-2 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-lg dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        @foreach(['pending' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $val => $label)
                            <option value="{{ $val }}" {{ $labRequest->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <form method="POST" action="{{ route('lab.requests.destroy', $labRequest) }}" onsubmit="return confirm('Hapus permintaan ini?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 border border-danger-200 dark:border-danger-800 text-danger-700 dark:text-danger-400 text-sm font-semibold rounded-xl hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-all duration-200">Hapus</button>
                    </form>
                    <x-primary-button type="submit">Simpan Hasil</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection