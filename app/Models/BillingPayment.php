<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPayment extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'billing_payments';

    protected $fillable = [
        'billing_id',
        'payment_method',
        'amount',
        'reference',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}