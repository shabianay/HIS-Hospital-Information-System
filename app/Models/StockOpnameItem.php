<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'stock_opname_items';

    protected $fillable = [
        'stock_opname_id',
        'medicine_id',
        'system_quantity',
        'actual_quantity',
        'difference',
        'notes',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'difference' => 'integer',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}