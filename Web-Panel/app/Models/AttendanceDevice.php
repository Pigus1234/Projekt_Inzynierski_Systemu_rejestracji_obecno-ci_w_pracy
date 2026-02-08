<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDevice extends Model
{
    protected $fillable = [
        'name',
        'api_token_hash',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];
}
