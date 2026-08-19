<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::withCount(['beds', 'admissions' => fn ($q) => $q->where('status', 'admitted')])
            ->latest()
            ->paginate(15);

        return view('inpatient.rooms.index', compact('rooms'));
    }

    public function indexCsv()
    {
        $this->authorize('viewAny', Room::class);

        $rooms = Room::withCount('beds')->orderBy('code')->get();

        $filename = 'data-kamar-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rooms) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA KAMAR RUMAH SAKIT HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Kode', 'Nama Kamar', 'Tipe', 'Tarif / Hari', 'Jumlah Tempat Tidur', 'Status']);
            foreach ($rooms as $room) {
                fputcsv($handle, [
                    $room->code,
                    $room->name,
                    Room::ROOM_TYPES[$room->room_type] ?? $room->room_type,
                    number_format((float) $room->price_per_day, 2),
                    $room->beds_count,
                    $room->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL KAMAR', $rooms->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', Room::class);

        return view('inpatient.rooms.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Room::class);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:rooms,code',
            'name' => 'required|string|max:100',
            'room_type' => 'required|in:vip,class_1,class_2,class_3,icu,hcu',
            'price_per_day' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function show(Room $room)
    {
        $this->authorize('view', $room);

        $room->load(['beds' => fn ($q) => $q->withCount(['admissions' => fn ($a) => $a->where('status', 'admitted')]), 'admissions' => fn ($q) => $q->active()->with(['patient', 'bed'])]);
        $totalBeds = $room->beds->count();
        $occupiedBeds = $room->beds->sum('admissions_count');

        return view('inpatient.rooms.show', compact('room', 'totalBeds', 'occupiedBeds'));
    }

    public function edit(Room $room)
    {
        $this->authorize('update', $room);

        return view('inpatient.rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $this->authorize('update', $room);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:rooms,code,' . $room->id,
            'name' => 'required|string|max:100',
            'room_type' => 'required|in:vip,class_1,class_2,class_3,icu,hcu',
            'price_per_day' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $room->update($validated);

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $this->authorize('delete', $room);

        if ($room->admissions()->exists()) {
            return redirect()->route('rooms.index')
                ->with('error', 'Kamar tidak dapat dihapus karena sudah memiliki riwayat perawatan.');
        }

        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Kamar berhasil dihapus.');
    }
}