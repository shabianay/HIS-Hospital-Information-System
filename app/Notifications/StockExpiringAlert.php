<?php

namespace App\Notifications;

use App\Models\MedicineStock;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockExpiringAlert extends Notification
{
    use Queueable;

    public function __construct(public MedicineStock $stock)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $expiryDate = $this->stock->expiry_date;
        $isExpired = $expiryDate ? $expiryDate->isPast() : false;
        $days = $expiryDate ? $expiryDate->diffInDays(now()) : null;

        return [
            'title' => $isExpired ? 'Obat Kedaluwarsa' : 'Obat Mendekati Kedaluwarsa',
            'message' => 'Batch ' . ($this->stock->batch_number ?: '-') . ' ' . ($this->stock->medicine?->name ?? 'obat') . ($isExpired ? ' sudah kedaluwarsa' : ' akan kedaluwarsa dalam ' . $days . ' hari') . ' (' . ($expiryDate?->format('d/m/Y') ?? '-') . ').',
            'url' => route('medicines.stock'),
        ];
    }
}