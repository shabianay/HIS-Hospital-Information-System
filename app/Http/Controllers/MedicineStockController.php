<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\StockMutation;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\StockExpiringAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineStockController extends Controller
{
    public function pending()
    {
        $this->authorize('update', Prescription::class);

        $prescriptions = Prescription::with(['medicine', 'medicalRecord.patient'])
            ->where('is_dispensed', false)
            ->whereHas('medicalRecord', fn ($q) => $q->whereHas('appointment', fn ($a) => $a->where('status', '!=', 'cancelled')))
            ->latest()
            ->get();

        $counts = [
            'total' => $prescriptions->count(),
            'medicines' => $prescriptions->unique('medicine_id')->count(),
        ];

        return view('pharmacy.pending', compact('prescriptions', 'counts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', MedicineStock::class);

        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'batch_number' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'required|date|after:today',
        ]);

        DB::transaction(function () use ($validated) {
            $stock = MedicineStock::firstOrNew(
                [
                    'medicine_id' => $validated['medicine_id'],
                    'batch_number' => $validated['batch_number'] ?? '',
                ]
            );

            $stock->expiry_date = $validated['expiry_date'];
            $stock->quantity = ($stock->quantity ?? 0) + $validated['quantity'];
            $stock->save();

            StockMutation::create([
                'medicine_id' => $validated['medicine_id'],
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'reference' => 'stock',
                'notes' => 'Stok masuk / pembelian',
            ]);
        });

        $this->notifyNearExpiry($validated['medicine_id']);

        return redirect()->route('medicines.stock')->with('success', 'Stok berhasil ditambahkan.');
    }

    private function notifyNearExpiry(int $medicineId): void
    {
        $nearExpiry = MedicineStock::with('medicine')
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(60)->endOfDay()])
            ->get();

        if ($nearExpiry->isEmpty()) {
            return;
        }

        $pharmacists = User::role('pharmacist')->get();
        foreach ($nearExpiry as $stock) {
            foreach ($pharmacists as $pharmacist) {
                if ($pharmacist->id !== auth()->id()) {
                    $pharmacist->notify(new StockExpiringAlert($stock));
                }
            }
        }
    }

    public function dispense($prescriptionId)
    {
        $this->authorize('update', Prescription::class);

        $prescription = Prescription::with('medicine')->findOrFail($prescriptionId);

        if ($prescription->is_dispensed) {
            return redirect()->back()->with('error', 'Resep telah didispensasi.');
        }

        try {
            DB::transaction(function () use ($prescription) {
                $locked = Prescription::whereKey($prescription->id)
                    ->where('is_dispensed', false)
                    ->lockForUpdate()
                    ->first();

                if (! $locked) {
                    return; // resep sudah didispensasi oleh request lain yang bersamaan
                }

                $quantityNeeded = $locked->quantity;

                $stocks = MedicineStock::where('medicine_id', $locked->medicine_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($stocks as $stock) {
                    if ($quantityNeeded <= 0) {
                        break;
                    }

                    $deducted = min($stock->quantity, $quantityNeeded);
                    $stock->decrement('quantity', $deducted);
                    $quantityNeeded -= $deducted;

                    StockMutation::create([
                        'medicine_id' => $locked->medicine_id,
                        'type' => 'out',
                        'quantity' => $deducted,
                        'reference' => "prescription#{$locked->id}",
                        'notes' => "Dispense resep #{$locked->id} ({$locked->medicine->name})",
                    ]);
                }

                if ($quantityNeeded > 0) {
                    throw new InsufficientStockException(
                        "Stok tidak mencukupi untuk {$locked->medicine->name}. Kurang {$quantityNeeded} unit."
                    );
                }

                $locked->update(['is_dispensed' => true]);
            });
        } catch (InsufficientStockException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $this->notifyLowStock($prescription->medicine_id);

        return redirect()->back()->with('success', 'Resep berhasil didispensasi.');
    }

    private function notifyLowStock(int $medicineId): void
    {
        $medicine = Medicine::find($medicineId);
        if (! $medicine) {
            return;
        }

        $remaining = MedicineStock::where('medicine_id', $medicineId)->sum('quantity');

        if ($remaining > $medicine->minimum_stock) {
            return;
        }

        $pharmacists = User::role('pharmacist')->get();
        foreach ($pharmacists as $pharmacist) {
            if ($pharmacist->id !== auth()->id()) {
                $pharmacist->notify(new LowStockAlert($medicine, (int) $remaining));
            }
        }
    }
}
