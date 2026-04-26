<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'target_amount', 'current_amount', 
        'emoji', 'color', 'deadline', 'is_active'
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline'       => 'date',
        'is_active'      => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function savingsEntries() {
        return $this->hasMany(SavingsEntry::class);
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_amount <= 0) return 0;
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
