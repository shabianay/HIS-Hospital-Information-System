<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Farmasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex h-full flex-col justify-between p-6 text-white">
    <div x-data="pharmacyBoard()" x-init="init()" class="flex h-full flex-col">
        {{-- Header --}}
        <header class="flex items-center justify-between border-b border-slate-700/50 pb-4">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-600 shadow-glass-sm">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">RUMAH SAKIT HIS</h1>
                    <p class="text-xs text-slate-400">Display Antrian Farmasi / Apotek</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-lg font-semibold text-primary-400" x-text="time">Memuat waktu...</span>
            </div>
        </header>

        {{-- Main --}}
        <main class="my-6 grid flex-1 grid-cols-12 gap-6">
            {{-- Left: Current queue call --}}
            <div class="col-span-8 flex flex-col items-center justify-between rounded-2xl border border-slate-700/50 bg-slate-800 p-8 text-center">
                <div>
                    <span class="text-xl font-semibold uppercase tracking-wider text-primary-400">Panggilan Antrian Obat</span>
                    <div class="mt-4 font-mono text-8xl font-black text-white" x-text="current ? (current.queue_number ?? String(activeIndex + 1).padStart(2, '0')) : '-'">-</div>
                    <div class="mt-4 text-3xl font-semibold text-slate-300">Silakan Menuju ke:</div>
                    <div class="mt-2 text-5xl font-extrabold text-primary-500" x-text="current ? current.patient_name : 'Menunggu panggilan...'">Menunggu panggilan...</div>
                </div>
                <div class="mt-6 w-full border-t border-slate-700/50 pt-6">
                    <p class="text-sm text-slate-400">Mohon serahkan resep atau lembar antrian Anda kepada petugas apotek.</p>
                </div>
            </div>

            {{-- Right: Queue list --}}
            <div class="col-span-4 flex flex-col gap-4">
                <div class="rounded-2xl border border-slate-700/50 bg-slate-800 p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-300">Antrean Menunggu</h3>
                        <span class="rounded-lg bg-slate-700 px-2 py-1 font-mono text-sm font-bold text-primary-400" x-text="queue.length">0</span>
                    </div>
                    <div class="mt-4 space-y-3 max-h-[70vh] overflow-y-auto">
                        <template x-if="queue.length === 0">
                            <div class="rounded-xl border border-slate-700/50 bg-slate-900/50 p-4 text-center text-slate-400">
                                Tidak ada resep dalam antrean.
                            </div>
                        </template>
                        <template x-for="(item, index) in queue.slice(0, 8)" :key="item.medical_record_id">
                            <div class="rounded-xl border border-slate-700/50 bg-slate-900/50 p-4" :class="{ 'border-primary-500/60': index === 0 }">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-2xl font-black text-primary-400" x-text="String(index + 1).padStart(2, '0')"></span>
                                    <span x-show="item.queue_number" class="rounded bg-slate-700 px-2 py-0.5 font-mono text-xs" x-text="'Antrian ' + item.queue_number"></span>
                                </div>
                                <p class="mt-1 truncate text-base font-semibold" x-text="item.patient_name"></p>
                                <div class="mt-2 space-y-1">
                                    <template x-for="(med, i) in item.items" :key="i">
                                        <div class="flex items-center justify-between text-xs text-slate-400">
                                            <span class="truncate" x-text="med.name"></span>
                                            <span class="ml-2 shrink-0 font-semibold text-slate-300" x-text="med.quantity + 'x'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer / Running Text --}}
        <footer class="overflow-hidden whitespace-nowrap rounded-2xl border border-primary-800/50 bg-primary-900/50 px-6 py-3">
            <div class="inline-block animate-marquee text-sm font-semibold text-primary-300">
                PENGUMUMAN: Obat hanya dapat diserahkan setelah pembayaran diselesaikan di kasir. Periksa kembali nama, dosis, dan cara pemakaian obat. Terima kasih atas kerja samanya.
            </div>
        </footer>
    </div>

    <script>
        function pharmacyBoard() {
            return {
                initial: @json($initial),
                queue: @json($initial['queue'] ?? []),
                time: '',
                timer: null,
                init() {
                    this.refreshClock();
                    setInterval(() => this.refreshClock(), 1000);
                    this.refresh();
                    this.timer = setInterval(() => this.refresh(), 5000);
                },
                refreshClock() {
                    let d = new Date();
                    this.time = d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' - ' + d.toLocaleTimeString('id-ID');
                },
                async refresh() {
                    try {
                        const res = await fetch('{{ route('queue.display.pharmacy.json') }}');
                        const data = await res.json();
                        if (data && Array.isArray(data.queue)) {
                            this.queue = data.queue;
                        }
                    } catch (e) {
                        // ignore transient network errors, keep current data
                    }
                },
                get current() {
                    return this.queue.length > 0 ? this.queue[0] : null;
                },
                get activeIndex() {
                    return 0;
                },
            };
        }
    </script>

    <style>
        @keyframes marquee {
            0% { transform: translate3d(100%, 0, 0); }
            100% { transform: translate3d(-100%, 0, 0); }
        }
        .animate-marquee {
            animation: marquee 25s linear infinite;
        }
    </style>
</body>
</html>