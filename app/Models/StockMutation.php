<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    use HasFactory;

    protected $table = 'stock_mutations';

    protected $fillable = [
        'medicine_id',
        'type',
        'quantity',
        'reference',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
