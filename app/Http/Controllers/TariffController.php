<?php

namespace App\Http\Controllers;

use App\Models\Poli;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Tariff::class);

        $tariffs = Tariff::with('poli')->latest()->paginate(15);

        return view('tariffs.index', compact('tariffs'));
    }

    public function indexCsv()
    {
        $this->authorize('viewAny', Tariff::class);

        $tariffs = Tariff::with('poli')->orderBy('name')->get();

        $filename = 'data-tarif-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($tariffs) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA TARIF RUMAH SAKIT HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            $typeLabels = ['konsultasi' => 'Konsultasi', 'tindakan' => 'Tindakan', 'penunjang' => 'Penunjang', 'lainnya' => 'Lainnya'];

            fputcsv($handle, ['Nama', 'Poli', 'Jenis', 'Harga', 'Status']);
            foreach ($tariffs as $tariff) {
                fputcsv($handle, [
                    $tariff->name,
                    $tariff->poli?->name ?? '-',
                    $typeLabels[$tariff->type] ?? $tariff->type,
                    number_format((float) $tariff->price, 2),
                    $tariff->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL TARIF', $tariffs->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', Tariff::class);

        $polis = Poli::orderBy('name')->get();

        return view('tariffs.create', compact('polis'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Tariff::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'poli_id' => 'nullable|exists:polis,id',
            'type' => 'required|in:konsultasi,tindakan,penunjang,lainnya',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Tariff::create($validated);

        return redirect()->route('tariffs.index')->with('success', 'Tarif berhasil ditambahkan.');
    }

    public function show(Tariff $tariff)
    {
        $this->authorize('view', $tariff);

        return view('tariffs.show', compact('tariff'));
    }

    public function edit(Tariff $tariff)
    {
        $this->authorize('update', $tariff);

        $polis = Poli::orderBy('name')->get();

        return view('tariffs.edit', compact('tariff', 'polis'));
    }

    public function update(Request $request, Tariff $tariff)
    {
        $this->authorize('update', $tariff);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'poli_id' => 'nullable|exists:polis,id',
            'type' => 'required|in:konsultasi,tindakan,penunjang,lainnya',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $tariff->update($validated);

        return redirect()->route('tariffs.index')->with('success', 'Tarif berhasil diperbarui.');
    }

    public function destroy(Tariff $tariff)
    {
        $this->authorize('delete', $tariff);

        $tariff->delete();

        return redirect()->route('tariffs.index')->with('success', 'Tarif berhasil dihapus.');
    }
}
