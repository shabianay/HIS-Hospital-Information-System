<?php

namespace App\Notifications;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrescriptionDispensed extends Notification
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
        $appointment = $this->prescription->medicalRecord?->appointment;

        return [
            'title' => 'Resep Telah Didispensasi',
            'message' => 'Resep ' . ($this->prescription->medicine?->name ?? '-') . ' untuk pasien ' . ($this->prescription->medicalRecord?->patient?->name ?? '-') . ' telah diserahkan ke farmasi. Siap untuk ditagih.',
            'url' => $appointment && $appointment->billing
                ? route('billings.show', $appointment->billing)
                : route('appointments.show', $appointment?->id ?? 0),
        ];
    }
}