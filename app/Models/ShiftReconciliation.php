<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftReconciliation extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'shift_reconciliations';

    protected $fillable = [
        'reconciliation_date',
        'shift',
        'opening_cash',
        'expected_cash',
        'counted_cash',
        'difference',
        'transaction_count',
        'notes',
        'prepared_by',
        'reconciled_at',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'opening_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'counted_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    public const SHIFTS = [
        'pagi' => 'Pagi (07:00 - 14:00)',
        'siang' => 'Siang (14:00 - 21:00)',
        'malam' => 'Malam (21:00 - 07:00)',
    ];

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}