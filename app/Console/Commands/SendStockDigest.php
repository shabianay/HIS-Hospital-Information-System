<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\StockExpiringAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendStockDigest extends Command
{
    protected $signature = 'stock:digest';

    protected $description = 'Notify pharmacists of low-stock and expiring medicines daily';

    public function handle(): int
    {
        $pharmacists = User::role('pharmacist')->get();
        if ($pharmacists->isEmpty()) {
            $this->info('No pharmacist users found.');

            return Command::SUCCESS;
        }

        $lowStockMedicines = Medicine::leftJoin('medicine_stocks', 'medicines.id', '=', 'medicine_stocks.medicine_id')
            ->select('medicines.id', 'medicines.name', 'medicines.unit', 'medicines.minimum_stock', DB::raw('COALESCE(SUM(medicine_stocks.quantity), 0) as total_stock'))
            ->groupBy('medicines.id', 'medicines.name', 'medicines.unit', 'medicines.minimum_stock')
            ->havingRaw('COALESCE(SUM(medicine_stocks.quantity), 0) <= medicines.minimum_stock')
            ->get();

        $expiringStocks = MedicineStock::with('medicine')
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays(60)->endOfDay()])
            ->get();

        $lowNotified = 0;
        $expiringNotified = 0;

        foreach ($lowStockMedicines as $medicine) {
            foreach ($pharmacists as $pharmacist) {
                $pharmacist->notify(new LowStockAlert($medicine, (int) $medicine->total_stock));
            }
            $lowNotified++;
        }

        foreach ($expiringStocks as $stock) {
            foreach ($pharmacists as $pharmacist) {
                $pharmacist->notify(new StockExpiringAlert($stock));
            }
            $expiringNotified++;
        }

        $this->info("Stock digest sent: {$lowNotified} low-stock medicines, {$expiringNotified} expiring stocks.");

        return Command::SUCCESS;
    }
}
