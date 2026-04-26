<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'threshold',
        'level',
        'sui_digest',
        'sui_object_id',
        'suivision_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
