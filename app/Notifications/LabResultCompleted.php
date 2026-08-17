<?php

namespace App\Notifications;

use App\Models\LabRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LabResultCompleted extends Notification
{
    use Queueable;

    public function __construct(public LabRequest $labRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Hasil Lab Selesai',
            'message' => 'Hasil lab untuk pasien ' . ($this->labRequest->patient?->name ?? '-') . ' sudah tersedia.' . ($this->labRequest->appointment?->billing ? '' : ' Biaya laboratorium siap ditagihkan.'),
            'url' => route('lab.requests.show', $this->labRequest),
        ];
    }
}