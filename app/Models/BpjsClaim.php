<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpjsClaim extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'bpjs_claims';

    protected $fillable = [
        'claim_number',
        'sep_record_id',
        'patient_id',
        'claim_date',
        'total_claim',
        'approved_amount',
        'status',
        'jenis_klaim',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'total_claim' => 'decimal:2',
        'approved_amount' => 'decimal:2',
    ];

    public const STATUSES = [
        'diajukan' => 'Diajukan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'menunggu' => 'Menunggu Verifikasi',
    ];

    public const JENIS_KLAIM = [
        'rawat_jalan' => 'Rawat Jalan',
        'rawat_inap' => 'Rawat Inap',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function sepRecord()
    {
        return $this->belongsTo(SepRecord::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}