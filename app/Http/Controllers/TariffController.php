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
