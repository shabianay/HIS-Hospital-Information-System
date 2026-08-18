<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Ruang Tunggu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $initialQueues = [];
    $allPoliNames = $queues->keys()->merge($currentInProgress->keys())->unique();
    foreach ($allPoliNames as $name) {
        $initialQueues[] = [
            'poli_id' => $queues->get($name)?->first()?->poli?->id ?? $currentInProgress->get($name)?->first()?->poli?->id,
            'poli_name' => $name,
            'poli_code' => $queues->get($name)?->first()?->poli?->code ?? $currentInProgress->get($name)?->first()?->poli?->code,
            'in_progress' => $currentInProgress->get($name, collect())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'queue_number' => $item->queue_number,
                    'patient_name' => $item->patient?->name,
                    'doctor_name' => $item->doctor?->name,
                ];
            })->values(),
            'waiting' => $queues->get($name, collect())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'queue_number' => $item->queue_number,
                    'patient_name' => $item->patient?->name,
                ];
            })->values(),
        ];
    }
@endphp
<body class="flex h-full flex-col justify-between bg-background-dark p-6 text-white">
    <div x-data="queueBoard()" x-init="init()" class="flex h-full flex-col">
        {{-- Header --}}
        <header class="flex items-center justify-between border-b border-border-dark/50 pb-4">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-600 shadow-glass-sm">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.128 12.12c-.08-.328-.21-.634-.383-.912l-1.025-1.637a2.25 2.25 0 0 1-.322-1.156V6.75A2.25 2.25 0 0 0 15.148 4.5H8.852A2.25 2.25 0 0 0 6.6 6.75v1.665c0 .416-.112.825-.322 1.156L5.253 11.2a2.25 2.25 0 0 1-.383.913A12.138 12.138 0 0 0 4.5 15.75c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125a12.138 12.138 0 0 0-.372-3.63Z"/></svg>
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">RUMAH SAKIT HIS</h1>
                    <p class="text-xs text-text-secondary-dark">Display Antrian Utama Pasien Rawat Jalan</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-lg font-semibold text-primary-400" x-text="time">Memuat waktu...</span>
            </div>
        </header>

        {{-- Main Grid --}}
        <main class="my-6 grid flex-1 grid-cols-12 gap-6">
            {{-- Left: Active Calling Queue --}}
            <div class="col-span-8 flex flex-col items-center justify-between rounded-2xl border border-border-dark/50 bg-surface-dark p-8 text-center">
                <div>
                    <span class="text-xl font-semibold uppercase tracking-wider text-primary-400">Panggilan Antrian Utama</span>
                    <div class="mt-4 font-mono text-9xl font-black text-white" x-text="activeCall ? activeCall.queue_number : '-'">-</div>
                    <div class="mt-4 text-3xl font-semibold text-secondary-300">Silakan Menuju ke:</div>
                    <div class="mt-2 text-5xl font-extrabold text-primary-500" x-text="activeCall ? activeCall.poli_name : 'Menunggu panggilan...'">Menunggu panggilan...</div>
                </div>
                <div class="mt-6 w-full border-t border-border-dark/50 pt-6">
                    <p class="text-sm text-text-secondary-dark">Mohon persiapkan kartu berobat atau lembar antrian Anda.</p>
                </div>
            </div>

            {{-- Right: Poli queues --}}
            <div class="col-span-4 flex flex-col gap-4">
                <template x-for="queue in queues" :key="queue.poli_id">
                    <div class="rounded-2xl border border-border-dark/50 bg-surface-dark p-5" :class="{ 'border-primary-500/50': queue.in_progress.length > 0 }">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-secondary-300" x-text="queue.poli_name"></h3>
                                <p class="text-xs text-text-secondary-dark">
                                    Sedang dilayani:
                                    <span class="font-semibold text-primary-400" x-text="queue.in_progress.length > 0 ? queue.in_progress[0].queue_number : 'Tidak ada'"></span>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-extrabold font-mono text-primary-400" x-text="queue.in_progress.length > 0 ? queue.in_progress[0].queue_number : '-'">-</span>
                            </div>
                        </div>
                        <div class="mt-3 border-t border-border-dark/50 pt-3">
                            <p class="mb-1 text-xs text-secondary-500">Antrean menunggu:</p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-if="queue.waiting.length > 0">
                                    <template x-for="item in queue.waiting.slice(0, 5)" :key="item.id">
                                        <span class="rounded-lg bg-secondary-700 px-2 py-1 font-mono text-xs" x-text="item.queue_number"></span>
                                    </template>
                                </template>
                                <template x-if="queue.waiting.length === 0">
                                    <span class="text-xs text-secondary-500">Kosong</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="queues.length === 0">
                    <div class="rounded-2xl border border-border-dark/50 bg-surface-dark p-6 text-center text-text-secondary-dark">
                        Belum ada antrean pada poli mana pun.
                    </div>
                </template>
            </div>
        </main>

        {{-- Footer / Running Text --}}
        <footer class="overflow-hidden whitespace-nowrap rounded-2xl border border-primary-800/50 bg-primary-900/50 px-6 py-3">
            <div class="inline-block animate-marquee text-sm font-semibold text-primary-300">
                PENGUMUMAN: Bagi pasien BPJS harap melakukan registrasi finger print di loket pendaftaran utama. Terima kasih atas kerja samanya. Tetap patuhi protokol kesehatan di area rumah sakit.
            </div>
        </footer>
    </div>

    <script>
        function queueBoard() {
            return {
                queues: @json($initialQueues),
                time: '',
                timer: null,
                lastAnnounced: '',
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
                        const res = await fetch('{{ route('queue.display.json') }}');
                        const data = await res.json();
                        if (Array.isArray(data)) {
                            const before = this.activeCall ? this.activeCall.queue_number : '';
                            this.queues = data;
                            const after = this.activeCall ? this.activeCall.queue_number : '';
                            if (after && after !== before && after !== this.lastAnnounced) {
                                this.lastAnnounced = after;
                                this.announce(after, this.activeCall.poli_name);
                            }
                        }
                    } catch (e) {
                        // ignore transient network errors, keep current data
                    }
                },
                announce(number, poli) {
                    if (!('speechSynthesis' in window)) return;
                    const msg = new SpeechSynthesisUtterance(
                        'Nomor antrian ' + number + ', silakan menuju ' + (poli || 'poli tujuan')
                    );
                    msg.lang = 'id-ID';
                    msg.rate = 0.9;
                    window.speechSynthesis.cancel();
                    window.speechSynthesis.speak(msg);
                },
                get activeCall() {
                    for (const queue of this.queues) {
                        if (queue.in_progress && queue.in_progress.length > 0) {
                            return {
                                queue_number: queue.in_progress[0].queue_number,
                                poli_name: queue.poli_name,
                            };
                        }
                    }
                    return null;
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
