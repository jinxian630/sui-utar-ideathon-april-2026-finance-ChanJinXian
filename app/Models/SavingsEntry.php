<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'savings_entries';

    protected $fillable = [
        'user_id', 'goal_id', 'type', 'amount', 'round_up_amount', 
        'note', 'description', 'category', 'synced_on_chain', 
        'sui_digest', 'staked', 'stake_digest', 'entry_date'
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'round_up_amount' => 'decimal:4',
        'synced_on_chain' => 'boolean',
        'staked'          => 'boolean',
        'entry_date'      => 'date',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function goal() {
        return $this->belongsTo(Goal::class);
    }
}
