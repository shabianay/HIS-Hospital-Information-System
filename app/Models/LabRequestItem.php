<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabRequestItem extends Model
{
    use HasFactory;

    protected $table = 'lab_request_items';

    protected $fillable = [
        'lab_request_id',
        'lab_test_id',
        'test_name',
        'unit',
        'reference_range',
        'price',
        'result_value',
        'result_status',
        'result_notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'result_status' => 'string',
    ];

    public function labRequest()
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }
}