<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Doctor::class);

        $query = Doctor::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $doctors = $query->latest()->paginate(15)->withQueryString();

        return view('doctors.index', compact('doctors'));
    }

    public function indexCsv(Request $request)
    {
        $this->authorize('viewAny', Doctor::class);

        $query = Doctor::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $doctors = $query->orderBy('name')->get();

        $filename = 'data-dokter-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($doctors) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA DOKTER RUMAH SAKIT HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Nama', 'Spesialisasi', 'No. SIP', 'Akun User', 'Status']);
            foreach ($doctors as $doctor) {
                fputcsv($handle, [
                    $doctor->name,
                    $doctor->specialization ?? '-',
                    $doctor->license_number,
                    $doctor->user?->email ?? '-',
                    $doctor->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL DOKTER', $doctors->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', Doctor::class);

        $availableUsers = $this->unlinkedDoctorUsers();

        return view('doctors.create', compact('availableUsers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Doctor::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:50|unique:doctors,license_number',
            'user_id' => ['nullable', 'exists:users,id', $this->userNotLinkedRule()],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Doctor::create($validated);

        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function show(Doctor $doctor)
    {
        $this->authorize('view', $doctor);

        $doctor->load('user');
        $schedules = $doctor->schedules()->with('poli')->latest()->get();

        return view('doctors.show', compact('doctor', 'schedules'));
    }

    public function edit(Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        $availableUsers = $this->unlinkedDoctorUsers()
            ->when($doctor->user_id, fn ($c) => $c->push($doctor->user));

        return view('doctors.edit', compact('doctor', 'availableUsers'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:50|unique:doctors,license_number,'.$doctor->id,
            'user_id' => ['nullable', 'exists:users,id', $this->userNotLinkedRule($doctor->id)],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $doctor->update($validated);

        return redirect()->route('doctors.show', $doctor)->with('success', 'Dokter berhasil diperbarui.');
    }

    public function destroy(Doctor $doctor)
    {
        $this->authorize('delete', $doctor);

        if ($doctor->appointments()->exists()) {
            return redirect()->route('doctors.index')
                ->with('error', 'Dokter tidak dapat dihapus karena memiliki riwayat kunjungan.');
        }

        $doctor->delete();

        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil dihapus.');
    }

    private function unlinkedDoctorUsers()
    {
        return User::role('doctor')
            ->whereDoesntHave('doctor')
            ->orderBy('name')
            ->get();
    }

    private function userNotLinkedRule(?int $exceptDoctorId = null)
    {
        return function ($attribute, $value, $fail) use ($exceptDoctorId) {
            if (! $value) {
                return;
            }

            $linked = Doctor::where('user_id', $value)
                ->when($exceptDoctorId, fn ($q) => $q->where('id', '!=', $exceptDoctorId))
                ->exists();

            if ($linked) {
                $fail('Akun user tersebut sudah tertaut ke dokter lain.');
            }
        };
    }
}
