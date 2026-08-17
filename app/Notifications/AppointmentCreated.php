<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentCreated extends Notification
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
            'title' => 'Janji Temu Baru',
            'message' => 'Pasien ' . ($this->appointment->patient?->name ?? '-') . ' dijadwalkan pada ' . ($this->appointment->appointment_date?->format('d M Y') ?? '-') . ' (antrian ' . $this->appointment->queue_number . ').',
            'url' => route('appointments.show', $this->appointment),
        ];
    }
}
