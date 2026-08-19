<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiologyTest extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'radiology_tests';

    protected $fillable = [
        'name',
        'category',
        'unit',
        'reference_range',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}