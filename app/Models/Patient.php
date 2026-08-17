<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'patients';

    protected $fillable = [
        'nik',
        'rm_number',
        'name',
        'date_of_birth',
        'gender',
        'address',
        'phone_number',
        'insurance_provider',
        'insurance_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }
}
