<?php

namespace App\Notifications;

use App\Models\LabRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LabRequestCreated extends Notification
{
    use Queueable;

    public function __construct(public LabRequest $labRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Permintaan Lab Baru #' . str_pad((string) $this->labRequest->id, 4, '0', STR_PAD_LEFT))
            ->greeting('Permintaan laboratorium baru masuk.')
            ->line('Pasien: ' . ($this->labRequest->patient?->name ?? '-'))
            ->line('Klik untuk melihat detail permintaan.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Permintaan Lab Baru',
            'message' => 'Permintaan lab untuk pasien ' . ($this->labRequest->patient?->name ?? '-') . '.',
            'url' => route('lab.requests.show', $this->labRequest),
        ];
    }
}