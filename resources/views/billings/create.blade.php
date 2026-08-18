@extends('layouts.app')
@section('title', 'Buat Tagihan Pasien')
@section('content')
<div class="w-full" x-data="{ consultation: {{ $consultationFee }}, medicineTotal: {{ $totalPrescription }}, labTotal: {{ $totalLab }}, actionTotal: 0, discount: 0, selectedTariffs: [], tariffs: {{ $tariffs->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'price' => (float) $t->price])->toJson() }}, get tariffTotal() { return this.selectedTariffs.reduce((sum, id) => { const t = this.tariffs.find(x => x.id === id); return sum + (t ? t.price : 0); }, 0); }, get total() { return Math.max(0, parseFloat(this.consultation || 0) + parseFloat(this.medicineTotal || 0) + parseFloat(this.labTotal || 0) + this.tariffTotal + parseFloat(this.actionTotal || 0) - parseFloat(this.discount || 0)); } }">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Buat Tagihan Pembayaran Pasien</h2>
        </div>
        <form action="{{ route('billings.store') }}" method="POST">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 gap-4 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-5 text-sm md:grid-cols-2">
                    <div>
                        <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Nama Pasien</span>
                        <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->patient?->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter Pemeriksa</span>
                        <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->doctor?->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Poli</span>
                        <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->poli?->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Pemeriksaan</span>
                        <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->appointment_date?->format('d/m/Y') }}</span>
                    </div>
                </div>

                @if($appointment->medicalRecord && $appointment->medicalRecord->prescriptions->isNotEmpty())
                <div class="rounded-xl border border-border-light dark:border-border-dark p-5">
                    <h4 class="mb-3 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Rincian Resep (untuk estimasi biaya obat)</h4>
                        <x-table :searchable="false">
                            <x-slot name="head">
                                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    <th class="pb-2 font-semibold">Obat</th>
                                    <th class="pb-2 font-semibold">Qty</th>
                                    <th class="pb-2 font-semibold">Harga Satuan</th>
                                    <th class="pb-2 font-semibold text-right">Subtotal</th>
                                </tr>
                            </x-slot>
                            @foreach($appointment->medicalRecord->prescriptions as $prescription)
                                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->medicine?->name }}</td>
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->quantity }}</td>
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($prescription->medicine?->sell_price ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3 text-right text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format(($prescription->medicine?->sell_price ?? 0) * $prescription->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </x-table>
                </div>
                @endif

                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <h3 class="mb-4 text-lg font-semibold text-primary-800 dark:text-primary-400">Rincian Biaya</h3>
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Biaya Konsultasi Dokter</span>
                            <div class="w-full md:w-48">
                                <input type="number" x-model.number="consultation" name="consultation_fee" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark text-right">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Biaya Obat-obatan & Alkes</span>
                            <div class="w-full md:w-48">
                                <input type="number" x-model.number="medicineTotal" name="medicine_fee" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark text-right">
                            </div>
                        </div>
                        @if($labItems->isNotEmpty())
                        <div class="rounded-xl border border-border-light dark:border-border-dark p-5">
                            <h4 class="mb-3 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Rincian Pemeriksaan Laboratorium</h4>
                                <x-table :searchable="false">
                                    <x-slot name="head">
                                        <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                            <th class="pb-2 font-semibold">Pemeriksaan</th>
                                            <th class="pb-2 font-semibold text-right">Harga</th>
                                        </tr>
                                    </x-slot>
                                    @foreach($labItems as $item)
                                        <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                            <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $item['test_name'] }}</td>
                                            <td class="py-3 text-right text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </x-table>
                        </div>
                        @endif
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Biaya Laboratorium</span>
                            <div class="w-full md:w-48">
                                <input type="number" x-model.number="labTotal" name="lab_fee" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark text-right">
                            </div>
                        </div>
                                                @if($tariffs->isNotEmpty())
                        <div class="rounded-xl border border-border-light dark:border-border-dark p-5">
                            <h4 class="mb-3 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Tarif Tindakan / Penunjang</h4>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <template x-for="tariff in tariffs" :key="tariff.id">
                                    <label class="flex items-center justify-between gap-3 rounded-lg border border-border-light dark:border-border-dark px-4 py-3 cursor-pointer hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                        <span class="flex items-center gap-3">
                                            <input type="checkbox" :value="tariff.id" name="tariff_ids[]" x-model="selectedTariffs" class="h-4 w-4 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500">
                                            <span class="text-sm font-medium text-text-primary-light dark:text-text-primary-dark" x-text="tariff.name"></span>
                                        </span>
                                        <span class="text-sm font-semibold text-primary-700 dark:text-primary-300" x-text="'Rp ' + tariff.price.toLocaleString('id-ID')"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                        @endif
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Biaya Tindakan / Penunjang (Tarif)</span>
                            <div class="w-full md:w-48">
                                <input type="number" x-model.number="actionTotal" name="action_fee" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark text-right">
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-medium text-danger-600 dark:text-danger-400">Diskon / Potongan</span>
                            <div class="w-full md:w-48">
                                <input type="number" x-model.number="discount" name="discount" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-danger-600 dark:text-danger-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark text-right">
                            </div>
                        </div>
                        <div>
                            <x-input-label for="notes" value="Catatan" />
                            <textarea name="notes" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between rounded-xl border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20 p-6">
                    <span class="text-xl font-bold text-primary-800 dark:text-primary-300">Total Tagihan:</span>
                    <span class="text-3xl font-extrabold text-primary-800 dark:text-primary-300" x-text="'Rp ' + Number(total).toLocaleString('id-ID')">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('appointments.show', $appointment) }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan Tagihan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
