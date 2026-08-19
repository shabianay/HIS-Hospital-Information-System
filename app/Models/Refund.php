<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'refunds';

    protected $fillable = [
        'refund_number',
        'billing_id',
        'patient_id',
        'amount',
        'reason',
        'notes',
        'processed_by',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public const REASONS = [
        'overpayment' => 'Kelebihan Pembayaran',
        'cancelled_service' => 'Layanan Dibatalkan',
        'billing_error' => 'Kesalahan Tagihan',
        'patient_refund' => 'Permintaan Pasien',
        'other' => 'Lainnya',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}