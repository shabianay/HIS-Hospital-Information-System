<?php

namespace App\Http\Controllers;

use App\Models\Icd9Procedure;
use Illuminate\Http\Request;

class Icd9ProcedureController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Icd9Procedure::class);

        $query = Icd9Procedure::orderBy('code');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        $procedures = $query->paginate(20)->withQueryString();

        return view('icd9.index', compact('procedures'));
    }

    public function indexCsv()
    {
        $this->authorize('viewAny', Icd9Procedure::class);

        $procedures = Icd9Procedure::orderBy('code')->get();

        $filename = 'master-icd9-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($procedures) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['MASTER ICD-9-CM PROSEDUR']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Kode', 'Nama Prosedur', 'Kategori', 'Deskripsi', 'Status']);
            foreach ($procedures as $proc) {
                fputcsv($handle, [
                    $proc->code,
                    $proc->name,
                    $proc->category ?? '-',
                    $proc->description ?? '-',
                    $proc->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL PROSEDUR', $procedures->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Icd9Procedure::class);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:icd9_procedures,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        Icd9Procedure::create($validated);

        return redirect()->route('icd9.index')->with('success', 'Prosedur ICD-9-CM berhasil ditambahkan.');
    }

    public function update(Request $request, Icd9Procedure $icd9Procedure)
    {
        $this->authorize('update', $icd9Procedure);

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:icd9_procedures,code,' . $icd9Procedure->id,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = (bool) $request->boolean('is_active');
        }

        $icd9Procedure->update($validated);

        return redirect()->route('icd9.index')->with('success', 'Prosedur ICD-9-CM berhasil diperbarui.');
    }

    public function destroy(Icd9Procedure $icd9Procedure)
    {
        $this->authorize('delete', $icd9Procedure);

        $icd9Procedure->delete();

        return redirect()->route('icd9.index')->with('success', 'Prosedur ICD-9-CM berhasil dihapus.');
    }
}