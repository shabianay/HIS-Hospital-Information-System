<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'opname_number',
        'opname_date',
        'status',
        'created_by_name',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'opname_date' => 'date',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'approved' => 'Disetujui',
    ];

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalDifferenceAttribute()
    {
        return (int) $this->items->sum('difference');
    }

    public function getHasDiscrepancyAttribute()
    {
        return $this->items->contains(fn ($item) => $item->difference != 0);
    }
}