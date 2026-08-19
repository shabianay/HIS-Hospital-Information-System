<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'created_by',
        'status',
        'order_date',
        'expected_date',
        'total_amount',
        'notes',
        'received_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'ordered' => 'Dipesan',
        'received' => 'Diterima',
        'cancelled' => 'Dibatalkan',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'ordered']);
    }
}