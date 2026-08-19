<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderReceived extends Notification
{
    use Queueable;

    public function __construct(public PurchaseOrder $purchaseOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Purchase Order Diterima ' . $this->purchaseOrder->po_number)
            ->greeting('Purchase order telah diterima.')
            ->line('Supplier: ' . ($this->purchaseOrder->supplier?->name ?? '-'))
            ->line('Total: Rp ' . number_format((float) $this->purchaseOrder->total_amount, 0, ',', '.'))
            ->line('Stok telah ditambahkan ke gudang.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Purchase Order Diterima',
            'message' => 'PO ' . $this->purchaseOrder->po_number . ' dari ' . ($this->purchaseOrder->supplier?->name ?? '-') . ' diterima, stok ditambahkan.',
            'url' => route('purchasing.orders.show', $this->purchaseOrder),
        ];
    }
}