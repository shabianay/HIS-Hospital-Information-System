<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeathCertificate extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'death_certificates';

    protected $fillable = [
        'certificate_number',
        'patient_id',
        'date_of_death',
        'place_of_death',
        'cause_of_death',
        'diagnosis',
        'deceased_relation',
        'reporter_name',
        'doctor_id',
        'doctor_name',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_of_death' => 'datetime',
    ];

    public const CAUSES = [
        'natural' => 'Sakit / Penyakit',
        'accident' => 'Kecelakaan',
        'cardiac' => 'Henti Jantung',
        'respiratory' => 'Gagal Napas',
        'other' => 'Lainnya',
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
}