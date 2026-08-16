<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Doctor::class);

        $query = Doctor::query();

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

    public function create()
    {
        $this->authorize('create', Doctor::class);

        return view('doctors.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Doctor::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:50|unique:doctors,license_number',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Doctor::create($validated);

        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function show(Doctor $doctor)
    {
        $this->authorize('view', $doctor);

        $schedules = $doctor->schedules()->with('poli')->latest()->get();

        return view('doctors.show', compact('doctor', 'schedules'));
    }

    public function edit(Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        return view('doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:50|unique:doctors,license_number,'.$doctor->id,
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
}
