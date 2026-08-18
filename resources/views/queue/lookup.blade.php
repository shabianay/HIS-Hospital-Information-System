<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Antrian | Rumah Sakit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-full bg-slate-100">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 text-white text-2xl font-extrabold shadow-glass-sm mb-4">RS</div>
                <h1 class="text-2xl font-extrabold text-slate-800">Cek Status Antrian</h1>
                <p class="mt-1 text-sm text-slate-500">Masukkan nomor antrian Anda untuk melihat posisi saat ini</p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-glass-sm p-6">
                <form method="POST" action="{{ route('queue.lookup.search') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="queue_number" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Antrian</label>
                        <input type="text" name="queue_number" id="queue_number" required
                            value="{{ old('queue_number') }}"
                            placeholder="Contoh: QUMU-20260818-001"
                            class="w-full border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl transition-all duration-200">
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl px-5 py-3 shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                        Cek Antrian
                    </button>
                </form>

                @if(session('error'))
                    <div class="mt-4 rounded-xl bg-danger-50 border border-danger-200 px-4 py-3 text-sm text-danger-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if(! empty($result))
                    <div class="mt-6 border-t border-slate-200 pt-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor Antrian</p>
                                <p class="mt-1 font-mono font-bold text-lg text-slate-800">{{ $result->queue_number }}</p>
                            </div>
                            @php
                                $badgeColors = [
                                    'Menunggu' => 'bg-warning-100 text-warning-700 border border-warning-200',
                                    'Sudah Hadir' => 'bg-info-100 text-info-700 border border-info-200',
                                    'Sedang Diperiksa' => 'bg-primary-100 text-primary-700 border border-primary-200',
                                    'Selesai' => 'bg-success-100 text-success-700 border border-success-200',
                                    'Dibatalkan' => 'bg-danger-100 text-danger-700 border border-danger-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColors[$statusLabel] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm">
                            <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3">
                                <span class="text-slate-500">Pasien</span>
                                <span class="font-semibold text-slate-800">{{ $result->patient?->name }}</span>
                            </div>
                            <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3">
                                <span class="text-slate-500">Poli</span>
                                <span class="font-semibold text-slate-800">{{ $result->poli?->name }}</span>
                            </div>
                            <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3">
                                <span class="text-slate-500">Dokter</span>
                                <span class="font-semibold text-slate-800">{{ $result->doctor?->name }}</span>
                            </div>
                        </div>

                        @if($result->status === 'waiting')
                            <div class="mt-4 rounded-xl bg-warning-50 border border-warning-200 px-4 py-3 text-sm text-warning-800">
                                <strong>Posisi Anda:</strong> menunggu {{ $ahead }} antrian sebelumnya.
                            </div>
                        @elseif($result->status === 'completed')
                            <div class="mt-4 rounded-xl bg-success-50 border border-success-200 px-4 py-3 text-sm text-success-800">
                                Pemeriksaan Anda telah selesai. Silakan menuju kasir untuk pembayaran jika ada tagihan.
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">Layanan informasi antrian pasien &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>