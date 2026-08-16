<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $table = 'prescriptions';

    protected $fillable = [
        'medical_record_id',
        'medicine_id',
        'quantity',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'is_dispensed',
    ];

    protected $casts = [
        'is_dispensed' => 'boolean',
        'quantity' => 'integer',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
