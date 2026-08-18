<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'billings';

    protected $fillable = [
        'invoice_number',
        'appointment_id',
        'patient_id',
        'total_amount',
        'paid_amount',
        'discount',
        'payment_method',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(BillingPayment::class);
    }

    public function getPaymentBreakdownAttribute()
    {
        return $this->payments
            ->groupBy('payment_method')
            ->mapWithKeys(fn ($items, $method) => [
                $method => [
                    'count' => $items->count(),
                    'total' => (float) $items->sum('amount'),
                ],
            ]);
    }
}
