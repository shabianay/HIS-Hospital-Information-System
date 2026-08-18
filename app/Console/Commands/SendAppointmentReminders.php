<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentReminder;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:remind';

    protected $description = 'Send tomorrow appointment reminders to doctors';

    public function handle(): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $appointments = Appointment::with(['doctor.user', 'patient', 'poli'])
            ->whereDate('appointment_date', $tomorrow)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->groupBy('doctor_id');

        $sent = 0;

        foreach ($appointments as $doctorId => $items) {
            $doctorUser = $items->first()->doctor?->user;
            if (! $doctorUser) {
                continue;
            }

            foreach ($items as $appointment) {
                $doctorUser->notify(new AppointmentReminder($appointment));
                $sent++;
            }
        }

        $this->info("Sent {$sent} appointment reminders for tomorrow.");

        return Command::SUCCESS;
    }
}
