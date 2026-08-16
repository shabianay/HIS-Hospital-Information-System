<?php

namespace App\Http\Controllers;

use App\Models\Poli;
use Illuminate\Http\Request;

class PoliController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Poli::class);

        $polis = Poli::latest()->paginate(15);

        return view('polis.index', compact('polis'));
    }

    public function create()
    {
        $this->authorize('create', Poli::class);

        return view('polis.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Poli::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:polis,name',
            'code' => 'required|string|max:10|unique:polis,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Poli::create($validated);

        return redirect()->route('polis.index')->with('success', 'Poli berhasil ditambahkan.');
    }

    public function show(Poli $poli)
    {
        $this->authorize('view', $poli);

        return view('polis.show', compact('poli'));
    }

    public function edit(Poli $poli)
    {
        $this->authorize('update', $poli);

        return view('polis.edit', compact('poli'));
    }

    public function update(Request $request, Poli $poli)
    {
        $this->authorize('update', $poli);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:polis,name,'.$poli->id,
            'code' => 'required|string|max:10|unique:polis,code,'.$poli->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $poli->update($validated);

        return redirect()->route('polis.index')->with('success', 'Poli berhasil diperbarui.');
    }

    public function destroy(Poli $poli)
    {
        $this->authorize('delete', $poli);

        if ($poli->schedules()->exists()) {
            return redirect()->route('polis.index')
                ->with('error', 'Poli tidak dapat dihapus karena memiliki jadwal.');
        }

        $poli->delete();

        return redirect()->route('polis.index')->with('success', 'Poli berhasil dihapus.');
    }
}
