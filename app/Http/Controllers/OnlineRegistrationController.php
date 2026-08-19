<?php

namespace App\Http\Controllers;

use App\Models\OnlineRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineRegistrationController extends Controller
{
    public function portal()
    {
        return view('portal.index');
    }

    public function book(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'nik' => 'nullable|string|digits_between:0,20',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:L,P',
            'poli' => 'required|in:' . implode(',', array_keys(OnlineRegistration::POLIS)),
            'complaint' => 'nullable|string|max:500',
            'registration_date' => 'required|date|after_or_equal:today',
        ]);

        $registration = DB::transaction(function () use ($validated) {
            $queueNumber = $this->nextQueueNumber($validated['poli'], $validated['registration_date']);

            return OnlineRegistration::create($validated + [
                'registration_number' => $this->generateRegistrationNumber(),
                'queue_number' => $queueNumber,
                'status' => 'registered',
            ]);
        });

        return redirect()->route('portal.status', ['registration_number' => $registration->registration_number])
            ->with('success', 'Pendaftaran antrian berhasil. Simpan nomor antrian Anda.');
    }

    public function lookup(Request $request)
    {
        $registrationNumber = $request->get('registration_number');

        $registration = null;
        if ($registrationNumber) {
            $registration = OnlineRegistration::where('registration_number', $registrationNumber)->first();
        }

        return view('portal.index', compact('registration', 'registrationNumber'));
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|exists:online_registrations,registration_number',
        ]);

        $registration = OnlineRegistration::where('registration_number', $validated['registration_number'])->firstOrFail();

        if (in_array($registration->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Antrian sudah selesai/dibatalkan.');
        }

        $registration->status = 'cancelled';
        $registration->save();

        return back()->with('success', 'Antrian ' . $registration->registration_number . ' dibatalkan.');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', OnlineRegistration::class);

        $query = OnlineRegistration::latest('registration_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('registration_date')) {
            $query->whereDate('registration_date', $request->registration_date);
        }

        $registrations = $query->paginate(15)->withQueryString();

        $summary = [
            'today' => OnlineRegistration::whereDate('registration_date', today())->count(),
            'waiting' => OnlineRegistration::whereDate('registration_date', today())->where('status', 'registered')->count(),
            'completed' => OnlineRegistration::whereDate('registration_date', today())->where('status', 'completed')->count(),
        ];

        return view('online-registrations.index', compact('registrations', 'summary'));
    }

    public function checkIn(OnlineRegistration $onlineRegistration)
    {
        $this->authorize('update', $onlineRegistration);

        if ($onlineRegistration->status !== 'registered') {
            return back()->with('error', 'Antrian ini sudah dikonfirmasi.');
        }

        $onlineRegistration->status = 'checked_in';
        $onlineRegistration->checked_in_at = now();
        $onlineRegistration->save();

        return back()->with('success', 'Pasien dikonfirmasi sudah datang.');
    }

    public function complete(OnlineRegistration $onlineRegistration)
    {
        $this->authorize('update', $onlineRegistration);

        $onlineRegistration->status = 'completed';
        $onlineRegistration->save();

        return back()->with('success', 'Antrian ditandai selesai.');
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', OnlineRegistration::class);

        $query = OnlineRegistration::latest('registration_date');

        if ($request->filled('registration_date')) {
            $query->whereDate('registration_date', $request->registration_date);
        }

        $registrations = $query->get();

        $statusLabels = OnlineRegistration::STATUSES;

        $filename = 'antrian-online-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($registrations, $statusLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA ANTRIAN ONLINE']);
            fputcsv($handle, ['No. Registrasi', 'Tanggal', 'No. Antrian', 'Pasien', 'Poli', 'Keluhan', 'Status']);
            foreach ($registrations as $reg) {
                fputcsv($handle, [
                    $reg->registration_number,
                    $reg->registration_date?->format('d/m/Y'),
                    $reg->queue_number,
                    $reg->patient_name,
                    OnlineRegistration::POLIS[$reg->poli] ?? $reg->poli,
                    $reg->complaint ?? '-',
                    $statusLabels[$reg->status] ?? $reg->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function generateRegistrationNumber(): string
    {
        $date = now()->format('Ymd');
        $last = OnlineRegistration::where('registration_number', 'like', 'AQ-' . $date . '-%')
            ->orderByDesc('registration_number')
            ->first();

        $seq = $last ? ((int) substr($last->registration_number, -4)) + 1 : 1;

        return 'AQ-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function nextQueueNumber(string $poli, string $date): string
    {
        $prefix = match ($poli) {
            'Umum' => 'A',
            'Penyakit Dalam' => 'B',
            'Anak' => 'C',
            'Gigi' => 'D',
            'Kandungan' => 'E',
            'Mata' => 'F',
            default => 'A',
        };

        $count = OnlineRegistration::where('poli', $poli)
            ->whereDate('registration_date', $date)
            ->count();

        return $prefix . '-' . str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);
    }
}