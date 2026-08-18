<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    use HasFactory;

    protected $table = 'vital_signs';

    protected $fillable = [
        'appointment_id',
        'recorded_by',
        'temperature',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'weight',
        'height',
        'oxygen_saturation',
        'notes',
    ];

    protected $casts = [
        'temperature' => 'float',
        'weight' => 'float',
        'height' => 'float',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
