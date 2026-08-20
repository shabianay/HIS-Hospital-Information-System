@extends('layouts.app')
@section('title', 'BPJS (SEP & Klaim)')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">BPJS (SEP & Klaim)</h2>
        <a href="{{ route('bpjs.csv') }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export Klaim CSV</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total SEP</p>
            <p class="mt-1 text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $summary['sep_count'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Klaim</p>
            <p class="mt-1 text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $summary['claim_count'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Diajukan</p>
            <p class="mt-1 text-xl font-bold text-danger-600 dark:text-danger-400">Rp {{ number_format($summary['total_claim'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Disetujui</p>
            <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($summary['approved_total'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div x-data="{ tab: 'sep' }">
        <div class="flex items-center gap-2 mb-4">
            <button type="button" @click="tab = 'sep'" :class="tab === 'sep' ? 'bg-primary-600 text-white' : 'bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark'" class="px-4 py-2 rounded-xl text-sm font-semibold">SEP</button>
            <button type="button" @click="tab = 'claims'" :class="tab === 'claims' ? 'bg-primary-600 text-white' : 'bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-text-secondary-light dark:text-text-secondary-dark'" class="px-4 py-2 rounded-xl text-sm font-semibold">Klaim</button>
            <div class="ml-auto flex gap-2">
                <button type="button" @click="$refs.sepModal.showModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl">+ SEP</button>
                <button type="button" @click="$refs.claimModal.showModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl">+ Klaim</button>
            </div>
        </div>

        <div x-show="tab === 'sep'" x-cloak>
            <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <x-table placeholder="Cari no. SEP / pasien / NIK...">
                    <x-slot name="head">
                        <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                            <th class="pb-4 px-4 font-semibold">No. SEP</th>
                            <th class="pb-4 px-4 font-semibold">Tanggal</th>
                            <th class="pb-4 px-4 font-semibold">Pasien</th>
                            <th class="pb-4 px-4 font-semibold">No. BPJS</th>
                            <th class="pb-4 px-4 font-semibold">Pelayanan</th>
                            <th class="pb-4 px-4 font-semibold">Poli</th>
                            <th class="pb-4 px-4 font-semibold">Status</th>
                            <th class="pb-4 px-4 font-semibold">Aksi</th>
                        </tr>
                    </x-slot>
                    @forelse($sepRecords as $sep)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $sep->sep_number }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $sep->sep_date?->format('d/m/Y') }}</td>
                        <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $sep->patient?->name }}</td>
                        <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $sep->bpjs_number }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\SepRecord::JENIS_PELAYANAN[$sep->jenis_pelayanan] ?? $sep->jenis_pelayanan }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $sep->poli ?? '-' }}</td>
                        <td class="py-4 px-4 text-sm">
                            @if($sep->status === 'aktif')
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">Aktif</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($sep->status === 'aktif')
                                <form method="POST" action="{{ route('bpjs.sep.cancel', $sep) }}" onsubmit="return confirm('Batalkan SEP ini?')">
                                    @csrf
                                    <x-action-link type="submit" variant="danger">Batalkan</x-action-link>
                                </form>
                            @else
                                <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="8" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada SEP.</td></tr>
                    @endforelse
                </x-table>
                <div class="mt-6">{{ $sepRecords->links() }}</div>
            </div>
        </div>

        <div x-show="tab === 'claims'" x-cloak>
            <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <x-table placeholder="Cari no. klaim / pasien...">
                    <x-slot name="head">
                        <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                            <th class="pb-4 px-4 font-semibold">No. Klaim</th>
                            <th class="pb-4 px-4 font-semibold">Tanggal</th>
                            <th class="pb-4 px-4 font-semibold">Pasien</th>
                            <th class="pb-4 px-4 font-semibold">No. SEP</th>
                            <th class="pb-4 px-4 font-semibold">Total</th>
                            <th class="pb-4 px-4 font-semibold">Disetujui</th>
                            <th class="pb-4 px-4 font-semibold">Status</th>
                            <th class="pb-4 px-4 font-semibold">Aksi</th>
                        </tr>
                    </x-slot>
                    @forelse($claims as $claim)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $claim->claim_number }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $claim->claim_date?->format('d/m/Y') }}</td>
                        <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $claim->patient?->name }}</td>
                        <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $claim->sepRecord?->sep_number ?? '-' }}</td>
                        <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($claim->total_claim, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-sm font-mono text-emerald-600 dark:text-emerald-400">{{ $claim->approved_amount !== null ? 'Rp ' . number_format($claim->approved_amount, 0, ',', '.') : '-' }}</td>
                        <td class="py-4 px-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($claim->status === 'disetujui') bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300
                                @elseif($claim->status === 'ditolak') bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300
                                @elseif($claim->status === 'menunggu') bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300
                                @else bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300 @endif">
                                {{ \App\Models\BpjsClaim::STATUSES[$claim->status] ?? $claim->status }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            @if($claim->status === 'diajukan' || $claim->status === 'menunggu')
                            <form method="POST" action="{{ route('bpjs.claim.status', $claim) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="status" class="text-xs border border-border-light bg-surface-light px-2 py-1 rounded-lg dark:border-border-dark dark:bg-surface-dark">
                                    @foreach(\App\Models\BpjsClaim::STATUSES as $val => $label)
                                        <option value="{{ $val }}" {{ $claim->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="approved_amount" min="0" step="0.01" placeholder="Rp disetujui" class="w-28 text-xs border border-border-light bg-surface-light px-2 py-1 rounded-lg dark:border-border-dark dark:bg-surface-dark" />
                                <x-action-link type="submit">Update</x-action-link>
                            </form>
                            @else
                                <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="8" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada klaim.</td></tr>
                    @endforelse
                </x-table>
                <div class="mt-6">{{ $claims->links() }}</div>
            </div>
        </div>
    </div>

    {{-- SEP modal --}}
    <dialog x-ref="sepModal" class="backdrop:bg-black/50 bg-transparent p-0 m-auto rounded-2xl max-w-lg w-full border-none">
        <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg p-8">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Buat SEP</h3>
            <form method="POST" action="{{ route('bpjs.sep.store') }}" class="grid grid-cols-1 gap-5">
                @csrf
                <div>
                    <x-input-label value="Pasien *" />
                    <select name="patient_id" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">-- Pilih Pasien --</option>
                        @foreach(\App\Models\Patient::orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->rm_number }} — {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="No. Kartu BPJS *" />
                    <x-text-input type="text" name="bpjs_number" required placeholder="Nomor kartu JKN" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Jenis Pelayanan *" />
                        <select name="jenis_pelayanan" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                            @foreach(\App\Models\SepRecord::JENIS_PELAYANAN as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Tanggal SEP *" />
                        <x-text-input type="date" name="sep_date" value="{{ now()->toDateString() }}" required />
                    </div>
                </div>
                <div>
                    <x-input-label value="Diagnosis" />
                    <x-text-input type="text" name="diagnosis" placeholder="Diagnosis awal" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Poli" />
                        <x-text-input type="text" name="poli" placeholder="Contoh: Penyakit Dalam" />
                    </div>
                    <div>
                        <x-input-label value="Faskes Perujuk" />
                        <x-text-input type="text" name="faskes_perujuk" placeholder="Contoh: Puskesmas" />
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="$refs.sepModal.close()" class="px-5 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Batal</button>
                    <x-primary-button type="submit">Simpan SEP</x-primary-button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Claim modal --}}
    <dialog x-ref="claimModal" class="backdrop:bg-black/50 bg-transparent p-0 m-auto rounded-2xl max-w-lg w-full border-none">
        <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg p-8">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Ajukan Klaim BPJS</h3>
            <form method="POST" action="{{ route('bpjs.claim.store') }}" class="grid grid-cols-1 gap-5">
                @csrf
                <div>
                    <x-input-label value="Pasien *" />
                    <select name="patient_id" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">-- Pilih Pasien --</option>
                        @foreach(\App\Models\Patient::orderBy('name')->get() as $p)
                            <option value="{{ $p->id }}">{{ $p->rm_number }} — {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="SEP (Opsional)" />
                    <select name="sep_record_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">-- Tanpa SEP --</option>
                        @foreach(\App\Models\SepRecord::with('patient')->where('status', 'aktif')->get() as $sep)
                            <option value="{{ $sep->id }}">{{ $sep->sep_number }} — {{ $sep->patient?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Tanggal Klaim *" />
                        <x-text-input type="date" name="claim_date" value="{{ now()->toDateString() }}" required />
                    </div>
                    <div>
                        <x-input-label value="Jenis Klaim *" />
                        <select name="jenis_klaim" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                            @foreach(\App\Models\BpjsClaim::JENIS_KLAIM as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <x-input-label value="Total Klaim (Rp) *" />
                    <x-text-input type="number" name="total_claim" min="0" step="0.01" required />
                </div>
                <div>
                    <x-input-label value="Catatan (Opsional)" />
                    <textarea name="notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="$refs.claimModal.close()" class="px-5 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Batal</button>
                    <x-primary-button type="submit">Ajukan Klaim</x-primary-button>
                </div>
            </form>
        </div>
    </dialog>
</div>
@endsection