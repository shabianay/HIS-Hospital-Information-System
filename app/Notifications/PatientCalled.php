<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PatientCalled extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pasien Dipanggil',
            'message' => 'Pasien ' . ($this->appointment->patient?->name ?? '-') . ' (antrian ' . $this->appointment->queue_number . ') telah dipanggil dan siap diperiksa di ' . ($this->appointment->poli?->name ?? '-') . '.',
            'url' => route('appointments.my-patients'),
        ];
    }
}
