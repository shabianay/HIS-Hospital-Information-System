<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification
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
            'title' => 'Pengingat Janji Temu',
            'message' => 'Besok Anda memiliki janji temu dengan pasien ' . ($this->appointment->patient?->name ?? '-') . ' (antrian ' . $this->appointment->queue_number . ') di ' . ($this->appointment->poli?->name ?? '-') . '.',
            'url' => route('appointments.my-patients'),
        ];
    }
}
