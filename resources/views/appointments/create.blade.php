@extends('layouts.app')
@section('title', 'Buat Janji Temu')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    {{-- Filter Jadwal --}}
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Pilih Dokter, Poli & Tanggal</h2>
        <form method="GET" action="{{ route('appointments.create') }}" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <x-input-label for="poli_id" value="Poli" />
                <select name="poli_id" onchange="this.form.submit()" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    <option value="">Pilih Poli</option>
                    @foreach($polis as $poli)
                        <option value="{{ $poli->id }}" {{ request('poli_id') == $poli->id ? 'selected' : '' }}>{{ $poli->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="doctor_id" value="Dokter" />
                <select name="doctor_id" onchange="this.form.submit()" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    <option value="">Pilih Dokter</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="appointment_date" value="Tanggal Kunjungan" />
                <x-text-input type="date" name="appointment_date" value="{{ request('appointment_date') }}" min="{{ date('Y-m-d') }}" onchange="this.form.submit()" />
            </div>
            <div class="md:col-span-3 flex justify-end">
                <x-primary-button type="submit">Cari Jadwal Tersedia</x-primary-button>
            </div>
        </form>
    </div>

    {{-- Form Pendaftaran --}}
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark mb-8">Registrasi Janji Temu Baru</h2>
        <form action="{{ route('appointments.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="patient_id" value="Pasien" />
                    <x-search-select
                        name="patient_id"
                        endpoint="{{ route('patients.search') }}"
                        placeholder="Cari berdasarkan nama/NIK/telepon..."
                        :selected-text="$patients->firstWhere('id', old('patient_id')) ? $patients->firstWhere('id', old('patient_id'))->name . ' — ' . $patients->firstWhere('id', old('patient_id'))->nik : null"
                    />
                    <x-input-error :messages="$errors->get('patient_id')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="poli_id" value="Poli" />
                        <select name="poli_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Poli</option>
                            @foreach($polis as $poli)
                                <option value="{{ $poli->id }}" {{ (old('poli_id') ?? request('poli_id')) == $poli->id ? 'selected' : '' }}>{{ $poli->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('poli_id')" />
                    </div>
                    <div>
                        <x-input-label for="doctor_id" value="Dokter" />
                        <select name="doctor_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Dokter</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ (old('doctor_id') ?? request('doctor_id')) == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('doctor_id')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="schedule_id" value="Jadwal (hari sesuai tanggal yang dipilih)" />
                    <select name="schedule_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih Jadwal</option>
                        @forelse($schedules as $schedule)
                            <option value="{{ $schedule->id }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                {{ ucfirst($schedule->day_of_week) }}, {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }} (Kuota: {{ $schedule->daily_quota }})
                            </option>
                        @empty
                            <option value="" disabled>Pilih poli, dokter, dan tanggal untuk melihat jadwal</option>
                        @endforelse
                    </select>
                    <x-input-error :messages="$errors->get('schedule_id')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="appointment_date" value="Tanggal Kunjungan" />
                        <x-text-input type="date" name="appointment_date" value="{{ old('appointment_date') ?? request('appointment_date') }}" min="{{ date('Y-m-d') }}" required />
                        <x-input-error :messages="$errors->get('appointment_date')" />
                    </div>
                    <div>
                        <x-input-label for="notes" value="Catatan (Opsional)" />
                        <x-text-input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Sakit kepala..." />
                        <x-input-error :messages="$errors->get('notes')" />
                    </div>
                </div>

                <div class="bg-primary-50 dark:bg-primary-900/30 rounded-2xl border border-primary-200 dark:border-primary-800 p-6">
                    <span class="text-sm font-bold text-primary-800 dark:text-primary-300">Estimasi Nomor Antrian:</span>
                    <p class="text-xs text-primary-600 dark:text-primary-400 mt-1">Nomor antrian dibuat otomatis berdasarkan kuota poli pada tanggal kunjungan.</p>
                </div>

                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('appointments.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Daftarkan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
