<?php

namespace App\Notifications;

use App\Models\Medicine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(public Medicine $medicine, public int $remainingStock)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Stok Obat Menipis',
            'message' => 'Stok ' . $this->medicine->name . ' tersisa ' . $this->remainingStock . ' ' . ($this->medicine->unit ?? 'unit') . ' (minimum ' . $this->medicine->minimum_stock . '). Segera lakukan pengadaan.',
            'url' => route('medicines.stock'),
        ];
    }
}