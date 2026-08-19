<?php

namespace App\Notifications;

use App\Models\RadiologyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RadiologyRequestCreated extends Notification
{
    use Queueable;

    public function __construct(public RadiologyRequest $radiologyRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Permintaan Radiologi Baru #' . str_pad((string) $this->radiologyRequest->id, 4, '0', STR_PAD_LEFT))
            ->greeting('Permintaan radiologi baru masuk.')
            ->line('Pasien: ' . ($this->radiologyRequest->patient?->name ?? '-'))
            ->line('Klik untuk melihat detail permintaan.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Permintaan Radiologi Baru',
            'message' => 'Permintaan radiologi untuk pasien ' . ($this->radiologyRequest->patient?->name ?? '-') . '.',
            'url' => route('radiology.requests.show', $this->radiologyRequest),
        ];
    }
}