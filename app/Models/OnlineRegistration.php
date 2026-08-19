<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineRegistration extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'online_registrations';

    protected $fillable = [
        'registration_number',
        'patient_name',
        'nik',
        'phone',
        'date_of_birth',
        'gender',
        'poli',
        'complaint',
        'registration_date',
        'queue_number',
        'status',
        'checked_in_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_date' => 'date',
        'checked_in_at' => 'datetime',
    ];

    public const STATUSES = [
        'registered' => 'Terdaftar',
        'checked_in' => 'Sudah Datang',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const POLIS = [
        'Umum' => 'Poli Umum',
        'Penyakit Dalam' => 'Poli Penyakit Dalam',
        'Anak' => 'Poli Anak',
        'Gigi' => 'Poli Gigi',
        'Kandungan' => 'Poli Kandungan',
        'Mata' => 'Poli Mata',
    ];
}