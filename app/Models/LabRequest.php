<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabRequest extends Model
{
    use HasFactory;

    protected $table = 'lab_requests';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'created_by',
        'is_urgent',
        'status',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'is_urgent' => 'boolean',
        'status' => 'string',
        'completed_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
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
        return $this->hasMany(LabRequestItem::class);
    }

    public function total()
    {
        return $this->items->sum(function ($item) {
            return $item->price;
        });
    }
}