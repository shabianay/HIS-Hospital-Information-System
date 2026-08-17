<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Medicine::class);

        $query = Medicine::withSum('stocks as total_stock', 'quantity');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        $medicines = $query->latest()->paginate(15)->withQueryString();

        return view('medicines.index', compact('medicines'));
    }

    public function create()
    {
        $this->authorize('create', Medicine::class);

        return view('medicines.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Medicine::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Medicine::create($validated);

        return redirect()->route('medicines.index')->with('success', 'Obat berhasil ditambahkan.');
    }

    public function show(Medicine $medicine)
    {
        $this->authorize('view', $medicine);

        $medicine->load('stocks');

        $totalStock = $medicine->stocks->sum('quantity');

        return view('medicines.show', compact('medicine', 'totalStock'));
    }

    public function edit(Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        return view('medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $this->authorize('update', $medicine);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'buy_price' => 'required|numeric|min:0',
            'sell_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $medicine->update($validated);

        return redirect()->route('medicines.index')->with('success', 'Obat berhasil diperbarui.');
    }

    public function destroy(Medicine $medicine)
    {
        $this->authorize('delete', $medicine);

        if ($medicine->prescriptions()->exists()) {
            return redirect()->route('medicines.index')
                ->with('error', 'Obat tidak dapat dihapus karena sudah digunakan pada resep.');
        }

        $medicine->stocks()->delete();
        $medicine->delete();

        return redirect()->route('medicines.index')->with('success', 'Obat berhasil dihapus.');
    }

    public function stock()
    {
        $this->authorize('viewAny', Medicine::class);

        $medicines = Medicine::with('stocks')
            ->withSum('stocks', 'quantity')
            ->orderBy('name')
            ->paginate(20);

        $lowStockMedicines = DB::table('medicines')
            ->leftJoin('medicine_stocks', 'medicines.id', '=', 'medicine_stocks.medicine_id')
            ->select('medicines.id', 'medicines.name', 'medicines.unit', 'medicines.minimum_stock', DB::raw('COALESCE(SUM(medicine_stocks.quantity), 0) as total_stock'))
            ->groupBy('medicines.id', 'medicines.name', 'medicines.unit', 'medicines.minimum_stock')
            ->havingRaw('COALESCE(SUM(medicine_stocks.quantity), 0) <= medicines.minimum_stock')
            ->get();

        return view('medicines.stock', compact('medicines', 'lowStockMedicines'));
    }

    public function mutations(Request $request)
    {
        $this->authorize('viewAny', Medicine::class);

        $query = StockMutation::with('medicine')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $mutations = $query->paginate(20)->withQueryString();
        $medicines = Medicine::orderBy('name')->get();

        return view('medicines.mutations', compact('mutations', 'medicines'));
    }

    public function reorder()
    {
        $this->authorize('viewAny', Medicine::class);

        $lowStock = DB::table('medicines as m')
            ->leftJoin('medicine_stocks as ms', 'm.id', '=', 'ms.medicine_id')
            ->select(
                'm.id',
                'm.name',
                'm.unit',
                'm.minimum_stock',
                'm.buy_price',
                DB::raw('COALESCE(SUM(ms.quantity), 0) as total_stock')
            )
            ->groupBy('m.id', 'm.name', 'm.unit', 'm.minimum_stock', 'm.buy_price')
            ->havingRaw('COALESCE(SUM(ms.quantity), 0) <= m.minimum_stock')
            ->orderByRaw('COALESCE(SUM(ms.quantity), 0) ASC')
            ->get()
            ->map(function ($item) {
                $item->suggested_quantity = max(0, $item->minimum_stock - $item->total_stock) + 10;
                $item->estimated_cost = (float) $item->suggested_quantity * (float) $item->buy_price;

                return $item;
            });

        $totalSuggestedCost = $lowStock->sum('estimated_cost');

        return view('medicines.reorder', compact('lowStock', 'totalSuggestedCost'));
    }
}
