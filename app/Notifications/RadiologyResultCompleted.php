<?php

namespace App\Notifications;

use App\Models\RadiologyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RadiologyResultCompleted extends Notification
{
    use Queueable;

    public function __construct(public RadiologyRequest $radiologyRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Hasil Radiologi Selesai',
            'message' => 'Hasil radiologi untuk pasien ' . ($this->radiologyRequest->patient?->name ?? '-') . ' sudah tersedia.' . ($this->radiologyRequest->appointment?->billing ? '' : ' Biaya pemeriksaan siap ditagihkan.'),
            'url' => route('radiology.requests.show', $this->radiologyRequest),
        ];
    }
}