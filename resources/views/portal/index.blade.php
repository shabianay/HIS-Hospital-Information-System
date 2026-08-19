@extends('layouts.portal')
@section('title', 'Antrian Online')
@section('content')
<div class="min-h-screen bg-gradient-to-br from-primary-50 via-surface-light to-primary-100 dark:from-primary-950 dark:via-surface-dark dark:to-primary-900 flex items-center justify-center p-6">
    <div class="w-full max-w-4xl grid lg:grid-cols-2 gap-8 items-start">
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">Antrian Online</h1>
                <p class="mt-2 text-text-secondary-light dark:text-text-secondary-dark">Ambil nomor antrian poliklinik tanpa harus datang lebih awal. Tunjukkan nomor antrian Anda di loket pendaftaran.</p>
            </div>

            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <h2 class="font-semibold text-text-primary-light dark:text-text-primary-dark mb-4">Cek Status Antrian</h2>
                <form method="GET" action="{{ route('portal.status') }}" class="flex flex-col sm:flex-row gap-3">
                    <x-text-input type="text" name="registration_number" value="{{ $registrationNumber ?? '' }}" placeholder="Masukkan No. Registrasi (AQ-...)" class="flex-1" />
                    <x-primary-button type="submit">Cek</x-primary-button>
                </form>

                @if($registration ?? null)
                <div class="mt-6 p-5 rounded-xl border border-border-light dark:border-border-dark bg-secondary-50/50 dark:bg-secondary-800/30">
                    <p class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">No. Registrasi</p>
                    <p class="text-lg font-mono font-bold text-text-primary-light dark:text-text-primary-dark">{{ $registration->registration_number }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Pasien</p>
                            <p class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $registration->patient_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Poli</p>
                            <p class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\OnlineRegistration::POLIS[$registration->poli] ?? $registration->poli }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">No. Antrian</p>
                            <p class="text-sm font-mono font-bold text-primary-600 dark:text-primary-400">{{ $registration->queue_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Status</p>
                            <p class="text-sm font-semibold {{ $registration->status === 'registered' ? 'text-warning-600 dark:text-warning-400' : ($registration->status === 'checked_in' ? 'text-primary-600 dark:text-primary-400' : ($registration->status === 'completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-danger-600 dark:text-danger-400')) }}">
                                {{ \App\Models\OnlineRegistration::STATUSES[$registration->status] ?? $registration->status }}
                            </p>
                        </div>
                    </div>
                    @if(in_array($registration->status, ['registered', 'checked_in']))
                    <form method="POST" action="{{ route('portal.cancel') }}" class="mt-4" onsubmit="return confirm('Batalkan antrian ini?')">
                        @csrf
                        <input type="hidden" name="registration_number" value="{{ $registration->registration_number }}" />
                        <button type="submit" class="text-xs font-semibold text-danger-600 hover:text-red-700 dark:text-red-400">Batalkan Antrian</button>
                    </form>
                    @endif
                </div>
                @elseif(($registrationNumber ?? null) && !($registration ?? null))
                <div class="mt-6 p-5 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 text-sm text-danger-700 dark:text-danger-300">
                    Nomor registrasi tidak ditemukan.
                </div>
                @endif
            </div>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg">
            <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Ambil Nomor Antrian</h2>

            @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('portal.book') }}" class="grid grid-cols-1 gap-4">
                @csrf
                <div>
                    <x-input-label value="Nama Lengkap *" />
                    <x-text-input type="text" name="patient_name" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="NIK" />
                        <x-text-input type="text" name="nik" />
                    </div>
                    <div>
                        <x-input-label value="No. HP" />
                        <x-text-input type="text" name="phone" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Jenis Kelamin *" />
                        <select name="gender" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Tanggal Lahir" />
                        <x-text-input type="date" name="date_of_birth" />
                    </div>
                </div>
                <div>
                    <x-input-label value="Poli Tujuan *" />
                    <select name="poli" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        @foreach(\App\Models\OnlineRegistration::POLIS as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Tanggal Kunjungan *" />
                    <x-text-input type="date" name="registration_date" value="{{ now()->toDateString() }}" required />
                </div>
                <div>
                    <x-input-label value="Keluhan" />
                    <textarea name="complaint" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
                </div>
                @if($errors->any())
                <div class="p-4 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 text-sm text-danger-700 dark:text-danger-300">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif
                <div class="pt-2">
                    <x-primary-button type="submit" class="w-full justify-center">Ambil Antrian</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection