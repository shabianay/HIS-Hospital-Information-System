@extends('layouts.app')
@section('title', 'Rekonsiliasi Kas per Shift')
@section('content')
<div class="space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Rekonsiliasi Kas per Shift</h3>
            <form method="GET" action="{{ route('billings.reconciliation') }}" class="flex items-center gap-3">
                <input type="date" name="date" value="{{ $date }}" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <x-primary-button type="submit">Tampilkan</x-primary-button>
            </form>
        </div>

        <div class="mb-6 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm">
            <span class="font-medium text-text-secondary-light dark:text-text-secondary-dark">Tanggal:</span>
            <strong class="text-text-primary-light dark:text-text-primary-dark">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>
            <span class="ml-4 text-xs text-text-secondary-light dark:text-text-secondary-dark">Shift malam dihitung dari pukul 21:00 tanggal tersebut hingga 07:00 hari berikutnya.</span>
        </div>

        <div class="grid grid-cols-1 gap-6">
            @foreach($shiftStats as $shift => $stats)
                <div class="rounded-2xl border border-border-light dark:border-border-dark overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-secondary-50 dark:bg-secondary-800/40 px-5 py-4">
                        <h4 class="font-bold text-text-primary-light dark:text-text-primary-dark">{{ $stats['label'] }}</h4>
                        @if($stats['reconciled'])
                            <span class="inline-flex items-center rounded-full bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 px-3 py-1 text-xs font-semibold border border-success-200 dark:border-success-800">Selesai Rekonsiliasi</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 px-3 py-1 text-xs font-semibold border border-yellow-200 dark:border-yellow-800">Belum Direkonsiliasi</span>
                        @endif
                    </div>

                    <div class="px-5 py-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Transaksi Tunai</p>
                            <p class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">{{ $stats['transaction_count'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Kas Masuk (Terhitung)</p>
                            <p class="text-lg font-bold text-primary-800 dark:text-primary-400">Rp {{ number_format($stats['expected_cash'], 0, ',', '.') }}</p>
                        </div>
                        @if($stats['reconciled'])
                            <div>
                                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Kas Awal</p>
                                <p class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($stats['reconciled']->opening_cash, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Selisih</p>
                                @php $diff = (float) $stats['reconciled']->difference; @endphp
                                <p class="text-lg font-bold {{ $diff == 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                    @if($diff == 0)
                                        Sesuai
                                    @elseif($diff > 0)
                                        + Rp {{ number_format($diff, 0, ',', '.') }} (lebih)
                                    @else
                                        - Rp {{ number_format(abs($diff), 0, ',', '.') }} (kurang)
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($stats['reconciled'])
                        <div class="px-5 pb-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                            <p>Kas awal: <strong>Rp {{ number_format($stats['reconciled']->opening_cash, 0, ',', '.') }}</strong> + Kas masuk: <strong>Rp {{ number_format($stats['reconciled']->expected_cash, 0, ',', '.') }}</strong> = Kas seharusnya: <strong>Rp {{ number_format((float) $stats['reconciled']->opening_cash + (float) $stats['reconciled']->expected_cash, 0, ',', '.') }}</strong></p>
                            <p>Kas dihitung fisik: <strong>Rp {{ number_format($stats['reconciled']->counted_cash, 0, ',', '.') }}</strong> · Dicatat oleh: {{ $stats['reconciled']->preparedBy?->name ?? '-' }} pada {{ $stats['reconciled']->reconciled_at?->format('d/m/Y H:i') }}</p>
                            @if($stats['reconciled']->notes)
                                <p class="mt-1">Catatan: {{ $stats['reconciled']->notes }}</p>
                            @endif
                        </div>
                        <div class="border-t border-border-light dark:border-border-dark px-5 py-3">
                            <button type="button" class="text-sm font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400"
                                onclick="document.getElementById('reconcil-{{ $shift }}').classList.toggle('hidden')">Perbarui / Periksa Ulang</button>
                        </div>
                    @endif

                    <div id="reconcil-{{ $shift }}" class="{{ $stats['reconciled'] ? 'hidden' : '' }} border-t border-border-light dark:border-border-dark px-5 py-4">
                        <form action="{{ route('billings.reconciliation.store', $shift) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <div>
                                <x-input-label for="counted_cash" value="Kas Fisik Dihitung (Rp)" />
                                <x-text-input type="number" step="0.01" min="0" name="counted_cash" value="{{ old('counted_cash', $stats['reconciled']?->counted_cash ?? $stats['expected_cash']) }}" required />
                                <x-input-error :messages="$errors->get('counted_cash')" />
                            </div>
                            <div class="md:col-span-1">
                                <x-input-label for="notes" value="Catatan" />
                                <input type="text" name="notes" value="{{ old('notes', $stats['reconciled']?->notes) }}" placeholder="Opsional" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" />
                            </div>
                            <div class="flex items-end">
                                <x-primary-button type="submit">{{ $stats['reconciled'] ? 'Perbarui' : 'Simpan Rekonsiliasi' }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection