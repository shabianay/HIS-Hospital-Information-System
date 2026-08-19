<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\StockMutation;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', StockOpname::class);

        $opnames = StockOpname::withCount('items')
            ->with(['createdBy'])
            ->latest('opname_date')
            ->paginate(15);

        $summary = [
            'count' => StockOpname::count(),
            'discrepancies' => StockOpname::whereHas('items', fn ($q) => $q->where('difference', '!=', 0))->count(),
        ];

        return view('stock-opname.index', compact('opnames', 'summary'));
    }

    public function create()
    {
        $this->authorize('create', StockOpname::class);

        $medicines = Medicine::with(['medicineStocks' => fn ($q) => $q->orderByDesc('expiry_date')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock-opname.create', compact('medicines'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', StockOpname::class);

        $validated = $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.actual_quantity' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $opname = DB::transaction(function () use ($validated) {
            $opname = StockOpname::create([
                'opname_number' => $this->generateOpnameNumber(),
                'opname_date' => $validated['opname_date'],
                'status' => 'draft',
                'created_by_name' => Auth::user()->name,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $systemQty = (int) MedicineStock::where('medicine_id', $item['medicine_id'])->sum('quantity');
                $actualQty = (int) $item['actual_quantity'];

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'medicine_id' => $item['medicine_id'],
                    'system_quantity' => $systemQty,
                    'actual_quantity' => $actualQty,
                    'difference' => $actualQty - $systemQty,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $opname;
        });

        return redirect()->route('stock-opname.index')->with('success', 'Stock opname berhasil dibuat (' . $opname->opname_number . ').');
    }

    public function show(StockOpname $stockOpname)
    {
        $this->authorize('view', $stockOpname);

        $stockOpname->load(['items.medicine', 'createdBy']);

        return view('stock-opname.show', compact('stockOpname'));
    }

    public function approve(StockOpname $stockOpname)
    {
        $this->authorize('update', $stockOpname);

        if ($stockOpname->status === 'approved') {
            return back()->with('error', 'Stock opname ini sudah disetujui.');
        }

        DB::transaction(function () use ($stockOpname) {
            foreach ($stockOpname->items as $item) {
                $diff = $item->difference;
                if ($diff === 0) {
                    continue;
                }

                $stock = MedicineStock::where('medicine_id', $item->medicine_id)
                    ->orderByDesc('expiry_date')
                    ->orderByDesc('quantity')
                    ->first();

                if ($stock) {
                    $newQty = max(0, $stock->quantity + $diff);
                    $stock->quantity = $newQty;
                    $stock->save();
                } elseif ($diff > 0) {
                    MedicineStock::create([
                        'medicine_id' => $item->medicine_id,
                        'batch_number' => 'OPNAME-' . $stockOpname->opname_number,
                        'quantity' => $diff,
                        'expiry_date' => now()->addYear(),
                    ]);
                }

                StockMutation::create([
                    'medicine_id' => $item->medicine_id,
                    'type' => $diff > 0 ? 'in' : 'out',
                    'quantity' => abs($diff),
                    'reference' => 'opname#' . $stockOpname->opname_number,
                    'notes' => 'Selisih stock opname: sistem ' . $item->system_quantity . ' vs aktual ' . $item->actual_quantity,
                ]);
            }

            $stockOpname->status = 'approved';
            $stockOpname->save();
        });

        return redirect()->route('stock-opname.show', $stockOpname)->with('success', 'Stock opname disetujui dan stok disesuaikan.');
    }

    public function destroy(StockOpname $stockOpname)
    {
        $this->authorize('delete', $stockOpname);

        if ($stockOpname->status === 'approved') {
            return back()->with('error', 'Stock opname yang sudah disetujui tidak dapat dihapus.');
        }

        $stockOpname->delete();

        return redirect()->route('stock-opname.index')->with('success', 'Stock opname dihapus.');
    }

    private function generateOpnameNumber(): string
    {
        $date = now()->format('Ymd');
        $last = StockOpname::where('opname_number', 'like', 'OPN-' . $date . '-%')
            ->orderByDesc('opname_number')
            ->first();

        $seq = $last ? ((int) substr($last->opname_number, -4)) + 1 : 1;

        return 'OPN-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}