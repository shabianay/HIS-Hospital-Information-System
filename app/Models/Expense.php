<?php

namespace App\Models;

use App\Audit\AuditsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use AuditsActivity, HasFactory;

    protected $table = 'expenses';

    protected $fillable = [
        'expense_number',
        'category',
        'description',
        'amount',
        'expense_date',
        'paid_to',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public const CATEGORIES = [
        'operasional' => 'Operasional',
        'utilitas' => 'Utilitas (Listrik, Air, dll)',
        'pemeliharaan' => 'Pemeliharaan',
        'gaji' => 'Gaji & Honor',
        'medis' => 'Perlengkapan Medis',
        'administrasi' => 'Administrasi',
        'lainnya' => 'Lainnya',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'bank' => 'Transfer Bank',
        'other' => 'Lainnya',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}