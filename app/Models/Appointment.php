<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'appointments';

    protected $fillable = [
        'queue_number',
        'patient_id',
        'doctor_id',
        'poli_id',
        'schedule_id',
        'appointment_date',
        'status',
        'consultation_fee',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'status' => 'string',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function billing()
    {
        return $this->hasOne(Billing::class);
    }
}
