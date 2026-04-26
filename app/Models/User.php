<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'wallet_address',
        'sui_address',
        'sui_finance_profile_id',
        'zk_pin_hash',
        'wallet_onboarded_at',
        'total_saved',
        'wallet_balance',
        'rebate_earned',
        'round_up_streak',
        'last_round_up_date',
        'kyc_status'
    ];

    /**
     * Get the user's savings entries.
     */
    public function savingsEntries()
    {
        return $this->hasMany(SavingsEntry::class);
    }

    /**
     * Get the user's goals.
     */
    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Get the user's badges.
     */
    public function badges()
    {
        return $this->hasMany(Badge::class);
    }

    /**
     * Get the user's AI financial consultant chat history.
     */
    public function chatLogs()
    {
        return $this->hasMany(ChatLog::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'wallet_onboarded_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
