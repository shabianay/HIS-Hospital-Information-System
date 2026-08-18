<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification
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
            'title' => 'Janji Temu Dibatalkan',
            'message' => 'Pasien ' . ($this->appointment->patient?->name ?? '-') . ' membatalkan janji temu antrian ' . $this->appointment->queue_number . ' pada ' . ($this->appointment->appointment_date?->format('d M Y') ?? '-') . '.',
            'url' => route('appointments.show', $this->appointment),
        ];
    }
}