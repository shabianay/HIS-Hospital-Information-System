<?php

namespace App\Notifications;

use App\Models\Surgery;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurgeryScheduled extends Notification
{
    use Queueable;

    public function __construct(public Surgery $surgery)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Jadwal Operasi Baru ' . $this->surgery->surgery_number)
            ->greeting('Jadwal operasi baru.')
            ->line('Pasien: ' . ($this->surgery->patient?->name ?? '-'))
            ->line('Prosedur: ' . $this->surgery->procedure_name)
            ->line('Terjadwal: ' . $this->surgery->scheduled_at?->format('d/m/Y H:i'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Jadwal Operasi Baru',
            'message' => 'Operasi ' . $this->surgery->procedure_name . ' untuk ' . ($this->surgery->patient?->name ?? '-') . ' terjadwal.',
            'url' => route('surgeries.show', $this->surgery),
        ];
    }
}