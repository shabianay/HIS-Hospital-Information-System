<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nomor Antrian {{ $appointment->queue_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
        }
        @page { margin: 8mm; }
    </style>
</head>
<body class="bg-background-light p-8">
<div class="mx-auto max-w-xs bg-surface-light p-6 shadow-glass-md border-2 border-dashed border-border-light">
    <div class="mb-4 text-center">
        <h2 class="text-base font-bold text-text-primary-light leading-tight">RUMAH SAKIT HIS</h2>
        <p class="text-xs text-text-secondary-light">Jl. Kesehatan No. 12, Jakarta</p>
        <p class="text-xs text-text-secondary-light">Telp: (021) 1234567</p>
    </div>

    <div class="mb-4 rounded-lg bg-primary-600 px-4 py-3 text-center text-white">
        <span class="block text-xs uppercase tracking-widest text-primary-100">Nomor Antrian</span>
        <span class="block font-mono text-3xl font-black">{{ $appointment->queue_number }}</span>
    </div>

    <div class="space-y-1 text-xs">
        <div class="flex justify-between"><span class="text-text-secondary-light">Tanggal:</span><strong>{{ $appointment->appointment_date?->format('d/m/Y') }}</strong></div>
        <div class="flex justify-between"><span class="text-text-secondary-light">Pasien:</span><strong>{{ $appointment->patient?->name }}</strong></div>
        <div class="flex justify-between"><span class="text-text-secondary-light">Poli:</span><strong>{{ $appointment->poli?->name }}</strong></div>
        <div class="flex justify-between"><span class="text-text-secondary-light">Dokter:</span><strong>{{ $appointment->doctor?->name }}</strong></div>
        <div class="flex justify-between"><span class="text-text-secondary-light">Jam Praktek:</span><strong>{{ $appointment->schedule ? $appointment->schedule->start_time->format('H:i') . ' - ' . $appointment->schedule->end_time->format('H:i') : '-' }}</strong></div>
    </div>

    <div class="mt-5 border-t-2 border-dashed border-border-light pt-4 text-center text-xs text-text-secondary-light">
        <p>Simpan tiket ini untuk registrasi ulang.</p>
        <p>Mohon menunggu panggilan antrian.</p>
    </div>

    <div class="no-print mt-6 flex justify-center gap-2">
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-semibold text-white shadow-glass-sm hover:bg-primary-700 transition-all duration-200">Cetak Tiket</button>
        <a href="{{ route('appointments.show', $appointment) }}" class="inline-flex items-center gap-2 rounded-xl border border-border-light bg-surface-light px-5 py-2.5 text-xs font-semibold text-text-primary-light hover:bg-secondary-50 transition-all duration-200">Kembali</a>
    </div>
</div>
<script>
    if (new URLSearchParams(window.location.search).get('auto') === '1') {
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
    }
</script>
</body>
</html>
