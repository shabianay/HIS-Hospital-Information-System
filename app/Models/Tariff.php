<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'tariffs';

    protected $fillable = [
        'name',
        'poli_id',
        'type',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }
}
