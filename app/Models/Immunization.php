<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Immunization extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'immunizations';

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'vaccine_name',
        'dose',
        'administered_at',
        'next_due_date',
        'batch_number',
        'site',
        'healthcare_worker',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'administered_at' => 'date',
        'next_due_date' => 'date',
    ];

    public const VACCINES = [
        'HB0' => 'Hepatitis B (HB-0)',
        'BCG' => 'BCG',
        'OPV' => 'Polio Oral (OPV)',
        'DPT' => 'DPT',
        'PCV' => 'PCV (Pneumokokus)',
        'MR' => 'MR / MMR',
        'JE' => 'JE (Japanese Encephalitis)',
        'Booster DPT' => 'Booster DPT',
        'Booster MR' => 'Booster MR',
        'TT' => 'Tetanus Toxoid (TT)',
        'Influenza' => 'Influenza',
        'COVID-19' => 'COVID-19',
        'Lainnya' => 'Lainnya',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}