<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'beds';

    protected $fillable = [
        'room_id',
        'bed_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function activeAdmission()
    {
        return $this->hasOne(Admission::class)->where('status', 'admitted');
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ! $this->admissions()->where('status', 'admitted')->exists();
    }
}