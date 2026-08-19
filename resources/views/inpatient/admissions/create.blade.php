@extends('layouts.app')
@section('title', 'Registrasi Rawat Inap')
@section('content')
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Registrasi Rawat Inap Baru</h2>
        </div>
        <form action="{{ route('admissions.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="patient_id" value="Pasien" />
                        <select name="patient_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Pasien</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->name }} ({{ $patient->rm_number ?: 'Tanpa RM' }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('patient_id')" />
                    </div>
                    <div>
                        <x-input-label for="doctor_id" value="Dokter Penanggung Jawab" />
                        <select name="doctor_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Dokter</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }} ({{ $doctor->specialization }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('doctor_id')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="admission_type" value="Tipe Admisi" />
                        <select name="admission_type" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            @foreach(\App\Models\Admission::ADMISSION_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ old('admission_type', 'elective') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('admission_type')" />
                    </div>
                    <div>
                        <x-input-label for="admitted_at" value="Tanggal Masuk" />
                        <x-text-input type="datetime-local" name="admitted_at" value="{{ old('admitted_at', now()->format('Y-m-d\TH:i')) }}" required />
                        <x-input-error :messages="$errors->get('admitted_at')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="room_id" value="Kamar" />
                        <select name="room_id" id="room_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Kamar</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" data-available="{{ $room->available_beds_count }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }} ({{ $room->code }}) - tersedia {{ $room->available_beds_count }}/{{ $room->beds_count }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('room_id')" />
                    </div>
                    <div>
                        <x-input-label for="bed_id" value="Tempat Tidur" />
                        <select name="bed_id" id="bed_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Kamar dahulu</option>
                        </select>
                        <x-input-error :messages="$errors->get('bed_id')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="diagnosis" value="Diagnosis Awal" />
                    <textarea name="diagnosis" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ old('diagnosis') }}</textarea>
                    <x-input-error :messages="$errors->get('diagnosis')" />
                </div>
                <div>
                    <x-input-label for="notes" value="Catatan" />
                    <textarea name="notes" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" />
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('admissions.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roomSelect = document.getElementById('room_id');
    const bedSelect = document.getElementById('bed_id');
    const bedsByRoom = @json($availableBedsByRoom);

    function populateBeds(roomId) {
        bedSelect.innerHTML = '<option value="">Pilih Tempat Tidur</option>';
        if (! roomId) return;
        const beds = bedsByRoom[roomId] || [];
        if (! beds.length) {
            bedSelect.innerHTML = '<option value="">Kamar penuh / tidak ada TT tersedia</option>';
            return;
        }
        beds.forEach(bed => {
            const opt = document.createElement('option');
            opt.value = bed.id;
            opt.textContent = 'TT ' + bed.bed_number;
            bedSelect.appendChild(opt);
        });
    }

    roomSelect.addEventListener('change', function () {
        populateBeds(this.value);
    });

    const initialRoom = roomSelect.value;
    if (initialRoom) populateBeds(initialRoom);
});
</script>
@endpush
@endsection