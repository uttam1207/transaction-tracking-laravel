<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'device', 'platform', 'browser',
        'location', 'country', 'city', 'is_successful', 'failure_reason',
        'token', 'logged_out_at',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'logged_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
