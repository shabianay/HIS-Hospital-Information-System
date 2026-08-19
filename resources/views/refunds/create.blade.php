@extends('layouts.app')
@section('title', 'Proses Refund')
@section('content')
<div class="w-full">
    <a href="{{ route('refunds.index') }}" class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:underline mb-6">← Kembali ke Daftar Refund</a>
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Proses Refund / Pengembalian Dana</h2>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 text-sm text-danger-700 dark:text-danger-300">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('refunds.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @csrf
            <div class="lg:col-span-2">
                <x-input-label value="Invoice (dengan kelebihan pembayaran) *" />
                <select name="billing_id" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    <option value="">-- Pilih Invoice --</option>
                    @foreach($billings as $b)
                        @php $kelebihan = (float) $b->paid_amount - (float) $b->total_amount; @endphp
                        @if($kelebihan > 0)
                        <option value="{{ $b->id }}" data-kelebihan="{{ $kelebihan }}" {{ $billing && $billing->id == $b->id ? 'selected' : '' }}>
                            {{ $b->invoice_number }} — {{ $b->patient?->name }} (Kelebihan: Rp {{ number_format($kelebihan, 0, ',', '.') }})
                        </option>
                        @endif
                    @endforeach
                </select>
                @error('billing_id') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <x-input-label value="Jumlah Refund (Rp) *" />
                <x-text-input type="number" name="amount" min="1" step="0.01" required placeholder="Maksimal = kelebihan pembayaran" />
                @error('amount') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <x-input-label value="Alasan Refund *" />
                <select name="reason" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    @foreach(\App\Models\Refund::REASONS as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <x-input-label value="Catatan (Opsional)" />
                <textarea name="notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
            </div>
            <div class="flex justify-end pt-4 lg:col-span-2">
                <x-primary-button type="submit">Proses Refund</x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection