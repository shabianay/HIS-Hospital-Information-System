<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'admissions';

    protected $fillable = [
        'admission_number',
        'patient_id',
        'doctor_id',
        'room_id',
        'bed_id',
        'admission_type',
        'status',
        'admitted_at',
        'discharged_at',
        'diagnosis',
        'discharge_reason',
        'notes',
        'admitted_by',
        'discharged_by',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];

    public const STATUSES = [
        'admitted' => 'Dirawat',
        'discharged' => 'Pulang',
        'cancelled' => 'Dibatalkan',
    ];

    public const ADMISSION_TYPES = [
        'elective' => 'Terencana (Elective)',
        'emergency' => 'Gawat Darurat (Emergency)',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function admittedBy()
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    public function dischargedBy()
    {
        return $this->belongsTo(User::class, 'discharged_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'admitted');
    }
}