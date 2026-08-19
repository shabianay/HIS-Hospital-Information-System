<?php

namespace App\Notifications;

use App\Models\EmergencyVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmergencyVisitCreated extends Notification
{
    use Queueable;

    public function __construct(public EmergencyVisit $emergencyVisit)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pasien IGD Baru ' . $this->emergencyVisit->visit_number)
            ->greeting('Pasien IGD baru masuk.')
            ->line('Pasien: ' . ($this->emergencyVisit->patient?->name ?? '-'))
            ->line('Triase: ' . (EmergencyVisit::TRIAGE_LEVELS[$this->emergencyVisit->triage_level] ?? $this->emergencyVisit->triage_level))
            ->line('Klik untuk melihat detail kunjungan.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pasien IGD Baru',
            'message' => 'Pasien IGD ' . ($this->emergencyVisit->patient?->name ?? '-') . ' dengan triase ' . ($this->emergencyVisit->triage_level === 'red' ? 'MERAH' : strtoupper($this->emergencyVisit->triage_level)) . '.',
            'url' => route('emergency.show', $this->emergencyVisit),
        ];
    }
}