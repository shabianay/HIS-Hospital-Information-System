<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\PurchaseOrderReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchasingController extends Controller
{
    // ─── Suppliers ─────────────────────────────────────────────────

    public function suppliers(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $query = Supplier::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('purchasing.suppliers', compact('suppliers'));
    }

    public function suppliersCsv()
    {
        $this->authorize('viewAny', Supplier::class);

        $suppliers = Supplier::orderBy('name')->get();

        $filename = 'data-supplier-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($suppliers) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA SUPPLIER']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Nama', 'Kontak Person', 'Telepon', 'Email', 'Alamat', 'Status']);
            foreach ($suppliers as $supplier) {
                fputcsv($handle, [
                    $supplier->name,
                    $supplier->contact_person ?? '-',
                    $supplier->phone ?? '-',
                    $supplier->email ?? '-',
                    $supplier->address ?? '-',
                    $supplier->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function supplierStore(Request $request)
    {
        $this->authorize('create', Supplier::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

        Supplier::create($validated);

        return redirect()->route('purchasing.suppliers')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function supplierUpdate(Request $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = (bool) $request->boolean('is_active');
        }

        $supplier->update($validated);

        return redirect()->route('purchasing.suppliers')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function supplierDestroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        if ($supplier->purchaseOrders()->exists()) {
            return redirect()->route('purchasing.suppliers')
                ->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki riwayat pembelian.');
        }

        $supplier->delete();

        return redirect()->route('purchasing.suppliers')->with('success', 'Supplier berhasil dihapus.');
    }

    // ─── Purchase Orders ──────────────────────────────────────────

    public function orders(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['supplier', 'createdBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(15)->withQueryString();

        $summary = [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'ordered' => PurchaseOrder::where('status', 'ordered')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'pending' => PurchaseOrder::whereIn('status', ['draft', 'ordered'])->count(),
        ];

        return view('purchasing.orders', compact('orders', 'summary'));
    }

    public function ordersCsv(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['supplier'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        $statusLabels = PurchaseOrder::STATUSES;

        $filename = 'purchase-orders-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($orders, $statusLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['PURCHASE ORDER']);
            fputcsv($handle, ['No. PO', 'Tanggal', 'Supplier', 'Total', 'Status']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->po_number,
                    $order->order_date?->format('d/m/Y'),
                    $order->supplier?->name ?? '-',
                    number_format((float) $order->total_amount, 2),
                    $statusLabels[$order->status] ?? $order->status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function ordersCreate()
    {
        $this->authorize('create', PurchaseOrder::class);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();

        return view('purchasing.orders-create', compact('suppliers', 'medicines'));
    }

    public function ordersStore(Request $request)
    {
        $this->authorize('create', PurchaseOrder::class);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $items = collect($validated['items'])
                ->filter(fn ($item) => ! empty($item['medicine_id']))
                ->map(fn ($item) => [
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'line_total' => (float) $item['quantity'] * (float) $item['unit_price'],
                ]);

            $total = $items->sum('line_total');

            $order = PurchaseOrder::create([
                'po_number' => $this->generatePoNumber(),
                'supplier_id' => $validated['supplier_id'],
                'created_by' => Auth::id(),
                'status' => 'draft',
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'total_amount' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                PurchaseOrderItem::create($item + ['purchase_order_id' => $order->id]);
            }

            return $order;
        });

        return redirect()->route('purchasing.orders.show', $order)
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function ordersShow(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load(['supplier', 'createdBy', 'items.medicine']);

        return view('purchasing.orders-show', compact('purchaseOrder'));
    }

    public function ordersStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        $validated = $request->validate([
            'status' => 'required|in:ordered,received,cancelled',
        ]);

        $status = $validated['status'];

        DB::transaction(function () use ($purchaseOrder, $status) {
            if ($status === 'received' && $purchaseOrder->status !== 'received') {
                foreach ($purchaseOrder->items as $item) {
                    $stock = MedicineStock::firstOrNew([
                        'medicine_id' => $item->medicine_id,
                        'batch_number' => 'PO-' . $purchaseOrder->id,
                    ]);
                    $stock->expiry_date = now()->addMonths(24)->toDateString();
                    $stock->quantity = ($stock->quantity ?? 0) + $item->quantity;
                    $stock->save();

                    StockMutation::create([
                        'medicine_id' => $item->medicine_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference' => "po#{$purchaseOrder->id}",
                        'notes' => "Penerimaan PO {$purchaseOrder->po_number}",
                    ]);
                }

                $purchaseOrder->received_at = now();
            }

            $purchaseOrder->status = $status;
            $purchaseOrder->save();
        });

        if ($status === 'received') {
            $recipients = collect();

            foreach (User::role('pharmacist')->get() as $pharmacist) {
                if ($pharmacist->id !== Auth::id()) {
                    $recipients->push($pharmacist);
                }
            }

            foreach (User::role('cashier')->get() as $cashier) {
                if ($cashier->id !== Auth::id()) {
                    $recipients->push($cashier);
                }
            }

            foreach ($recipients->unique('id') as $recipient) {
                $recipient->notify(new PurchaseOrderReceived($purchaseOrder));
            }
        }

        $this->forgetDashboardCache();

        return redirect()->route('purchasing.orders.show', $purchaseOrder)
            ->with('success', 'Status Purchase Order berhasil diperbarui.');
    }

    public function ordersDestroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('delete', $purchaseOrder);

        if ($purchaseOrder->status === 'received') {
            return redirect()->route('purchasing.orders.show', $purchaseOrder)
                ->with('error', 'PO yang sudah diterima tidak dapat dihapus.');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchasing.orders')->with('success', 'Purchase Order dihapus.');
    }

    private function generatePoNumber(): string
    {
        $date = now()->format('Ymd');
        $last = PurchaseOrder::where('po_number', 'like', 'PO-' . $date . '-%')
            ->orderByDesc('po_number')
            ->first();

        $seq = $last ? ((int) substr($last->po_number, -4)) + 1 : 1;

        return 'PO-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('dashboard.' . now()->format('Y-m-d'));
    }
}