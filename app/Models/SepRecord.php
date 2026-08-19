<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SepRecord extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'sep_records';

    protected $fillable = [
        'sep_number',
        'patient_id',
        'appointment_id',
        'bpjs_number',
        'jenis_pelayanan',
        'sep_date',
        'diagnosis',
        'poli',
        'faskes_perujuk',
        'status',
        'created_by',
    ];

    protected $casts = [
        'sep_date' => 'date',
    ];

    public const JENIS_PELAYANAN = [
        'rawat_jalan' => 'Rawat Jalan',
        'rawat_inap' => 'Rawat Inap',
    ];

    public const STATUSES = [
        'aktif' => 'Aktif',
        'dibatalkan' => 'Dibatalkan',
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

    public function claims()
    {
        return $this->hasMany(BpjsClaim::class, 'sep_record_id');
    }
}