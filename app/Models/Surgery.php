<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surgery extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'surgeries';

    protected $fillable = [
        'surgery_number',
        'patient_id',
        'doctor_id',
        'icd9_procedure_id',
        'created_by',
        'procedure_name',
        'surgery_type',
        'operating_room',
        'status',
        'pre_notes',
        'post_notes',
        'scheduled_at',
        'started_at',
        'finished_at',
        'completed_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public const STATUSES = [
        'scheduled' => 'Terjadwal',
        'in_progress' => 'Sedang Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const TYPES = [
        'minor' => 'Bedah Minor',
        'major' => 'Bedah Mayor',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function icd9Procedure()
    {
        return $this->belongsTo(Icd9Procedure::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_progress']);
    }
}