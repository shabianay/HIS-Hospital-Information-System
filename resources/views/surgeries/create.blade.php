@extends('layouts.app')
@section('title', 'Buat Jadwal Operasi')
@section('content')
<div class="w-full space-y-6">
    <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Buat Jadwal Operasi</h2>

    <form action="{{ route('surgeries.store') }}" method="POST" class="grid grid-cols-1 gap-6">
        @csrf

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Pasien & Operator</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <x-input-label value="Pasien *" />
                    <select name="patient_id" required
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Pilih pasien...</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ $preselectedPatient && $preselectedPatient->id === $patient->id ? 'selected' : '' }}>{{ $patient->name }} ({{ $patient->patient_number }})</option>
                        @endforeach
                    </select>
                    @error('patient_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label value="Dokter Operator" />
                    <select name="doctor_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Pilih dokter...</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Prosedur Operasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <x-input-label value="Prosedur ICD-9-CM" />
                    <select name="icd9_procedure_id" id="icd9_select"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Pilih prosedur ICD-9-CM...</option>
                        @foreach($procedures as $proc)
                        <option value="{{ $proc->id }}" data-name="{{ $proc->name }}">{{ $proc->code }} - {{ $proc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Nama Prosedur *" />
                    <x-text-input type="text" name="procedure_name" id="procedure_name" required placeholder="Contoh: Laparoscopic Cholecystectomy" />
                </div>
                <div>
                    <x-input-label value="Jenis Operasi *" />
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                            <input type="radio" name="surgery_type" value="minor" checked class="h-4 w-4 border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-surface-dark dark:ring-offset-background-dark">
                            Bedah Minor
                        </label>
                        <label class="flex items-center gap-2 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                            <input type="radio" name="surgery_type" value="major" class="h-4 w-4 border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-surface-dark dark:ring-offset-background-dark">
                            Bedah Mayor
                        </label>
                    </div>
                </div>
                <div>
                    <x-input-label value="Kamar Operasi" />
                    <x-text-input type="text" name="operating_room" placeholder="Contoh: OK 1" />
                </div>
                <div>
                    <x-input-label value="Jadwal Operasi *" />
                    <x-text-input type="datetime-local" name="scheduled_at" required />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label value="Catatan Persiapan (Opsional)" />
                <textarea name="pre_notes" rows="3" placeholder="Puasa, alergi obat, persiapan darah..." class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <x-secondary-button href="{{ route('surgeries.index') }}">Batal</x-secondary-button>
            <x-primary-button type="submit">Simpan Jadwal Operasi</x-primary-button>
        </div>
    </form>
</div>
@push('scripts')
<script>
document.getElementById('icd9_select').addEventListener('change', function () {
    var name = this.options[this.selectedIndex].dataset.name;
    if (name) {
        document.getElementById('procedure_name').value = name;
    }
});
</script>
@endpush
@endsection