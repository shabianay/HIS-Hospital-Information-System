<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicines';

    protected $fillable = [
        'name',
        'generic_name',
        'category',
        'unit',
        'buy_price',
        'sell_price',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function medicineStocks()
    {
        return $this->hasMany(MedicineStock::class);
    }

    public function stocks()
    {
        return $this->hasMany(MedicineStock::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }
}
