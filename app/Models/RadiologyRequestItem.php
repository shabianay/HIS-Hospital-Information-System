<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiologyRequestItem extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'radiology_request_items';

    protected $fillable = [
        'radiology_request_id',
        'radiology_test_id',
        'test_name',
        'reference_range',
        'price',
        'result_findings',
        'result_impression',
        'result_status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(RadiologyRequest::class, 'radiology_request_id');
    }

    public function test()
    {
        return $this->belongsTo(RadiologyTest::class, 'radiology_test_id');
    }
}