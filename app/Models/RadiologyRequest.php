<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiologyRequest extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'radiology_requests';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'created_by',
        'is_urgent',
        'status',
        'clinical_notes',
        'completed_at',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'Menunggu',
        'in_progress' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(RadiologyRequestItem::class);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
}