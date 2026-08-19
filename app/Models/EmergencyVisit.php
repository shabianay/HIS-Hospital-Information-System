<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyVisit extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'emergency_visits';

    protected $fillable = [
        'visit_number',
        'patient_id',
        'doctor_id',
        'created_by',
        'triage_level',
        'chief_complaint',
        'triage_notes',
        'temperature',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'gcs',
        'status',
        'referred_to',
        'discharge_notes',
        'arrived_at',
        'discharged_at',
        'discharged_by',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'arrived_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];

    public const TRIAGE_LEVELS = [
        'red' => 'Merah (Resusitasi)',
        'yellow' => 'Kuning (Urgent)',
        'green' => 'Hijau (Non-Urgent)',
        'black' => 'Hitam (Meninggal)',
    ];

    public const STATUSES = [
        'waiting' => 'Menunggu',
        'in_triage' => 'Dalam Triase',
        'treatment' => 'Perawatan',
        'observation' => 'Observasi',
        'admitted' => 'Rawat Inap',
        'discharged' => 'Pulang',
        'referred' => 'Dirujuk',
        'deceased' => 'Meninggal',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dischargedBy()
    {
        return $this->belongsTo(User::class, 'discharged_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['waiting', 'in_triage', 'treatment', 'observation']);
    }
}