<?php

namespace App\Notifications;

use App\Models\Billing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillingCreated extends Notification
{
    use Queueable;

    public function __construct(public Billing $billing)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tagihan Baru',
            'message' => 'Tagihan ' . $this->billing->invoice_number . ' sebesar Rp ' . number_format((float) $this->billing->total_amount, 0, ',', '.') . ' untuk pasien ' . ($this->billing->patient?->name ?? '-') . '.',
            'url' => route('billings.show', $this->billing),
        ];
    }
}
