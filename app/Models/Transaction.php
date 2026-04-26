<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    // This tells Laravel to use your 'transaction' table instead of 'transactions'
    protected $table = 'transaction';

    // Optional: If your table doesn't have 'created_at' and 'updated_at' columns, add this:
    public $timestamps = false;

    // Allow mass assignment for these fields (prevents MassAssignmentException)
    protected $fillable = ['user_id', 'savings_entry_id', 'description', 'amount', 'type'];

    public function savingsEntry(): BelongsTo
    {
        return $this->belongsTo(SavingsEntry::class);
    }
}
