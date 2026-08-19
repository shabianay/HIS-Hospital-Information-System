<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Bed::class);

        $beds = Bed::with(['room' => fn ($q) => $q->select('id', 'code', 'name')])
            ->withCount(['admissions' => fn ($q) => $q->where('status', 'admitted')])
            ->latest()
            ->paginate(15);

        return view('inpatient.beds.index', compact('beds'));
    }

    public function indexCsv()
    {
        $this->authorize('viewAny', Bed::class);

        $beds = Bed::with('room')->orderBy('room_id')->orderBy('bed_number')->get();

        $filename = 'data-tempat-tidur-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($beds) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA TEMPAT TIDUR RUMAH SAKIT HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Kamar', 'No. Tempat Tidur', 'Status']);
            foreach ($beds as $bed) {
                fputcsv($handle, [
                    $bed->room?->name ?? '-',
                    $bed->bed_number,
                    $bed->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL TEMPAT TIDUR', $beds->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', Bed::class);

        $rooms = Room::where('is_active', true)->orderBy('code')->get();

        return view('inpatient.beds.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Bed::class);

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $existing = Bed::where('room_id', $validated['room_id'])
            ->where('bed_number', $validated['bed_number'])
            ->exists();

        if ($existing) {
            return back()->withErrors(['bed_number' => 'Nomor tempat tidur sudah ada di kamar ini.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active');

        Bed::create($validated);

        return redirect()->route('beds.index')->with('success', 'Tempat tidur berhasil ditambahkan.');
    }

    public function edit(Bed $bed)
    {
        $this->authorize('update', $bed);

        $rooms = Room::where('is_active', true)->orderBy('code')->get();

        return view('inpatient.beds.edit', compact('bed', 'rooms'));
    }

    public function update(Request $request, Bed $bed)
    {
        $this->authorize('update', $bed);

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $existing = Bed::where('room_id', $validated['room_id'])
            ->where('bed_number', $validated['bed_number'])
            ->where('id', '!=', $bed->id)
            ->exists();

        if ($existing) {
            return back()->withErrors(['bed_number' => 'Nomor tempat tidur sudah ada di kamar ini.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active');

        $bed->update($validated);

        return redirect()->route('beds.index')->with('success', 'Tempat tidur berhasil diperbarui.');
    }

    public function destroy(Bed $bed)
    {
        $this->authorize('delete', $bed);

        if ($bed->admissions()->exists()) {
            return redirect()->route('beds.index')
                ->with('error', 'Tempat tidur tidak dapat dihapus karena sudah memiliki riwayat perawatan.');
        }

        $bed->delete();

        return redirect()->route('beds.index')->with('success', 'Tempat tidur berhasil dihapus.');
    }
}