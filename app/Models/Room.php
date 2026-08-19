<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'code',
        'name',
        'room_type',
        'price_per_day',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price_per_day' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const ROOM_TYPES = [
        'vip' => 'VIP',
        'class_1' => 'Kelas 1',
        'class_2' => 'Kelas 2',
        'class_3' => 'Kelas 3',
        'icu' => 'ICU',
        'hcu' => 'HCU',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function availableBeds()
    {
        return $this->beds()
            ->where('is_active', true)
            ->whereDoesntHave('admissions', fn ($q) => $q->where('status', 'admitted'));
    }
}