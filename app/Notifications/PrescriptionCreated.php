<?php

namespace App\Notifications;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrescriptionCreated extends Notification
{
    use Queueable;

    public function __construct(public Prescription $prescription)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Resep Baru',
            'message' => 'Resep ' . ($this->prescription->medicine?->name ?? '-') . ' untuk pasien ' . ($this->prescription->medicalRecord?->patient?->name ?? '-') . ' menunggu dispensasi.',
            'url' => route('prescriptions.pending'),
        ];
    }
}
