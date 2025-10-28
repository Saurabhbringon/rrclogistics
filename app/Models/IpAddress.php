<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'status',
        'description',
        'security_status',
        'security_status_updated_at',
    ];

    protected $casts = [
        'security_status_updated_at' => 'datetime',
    ];
}
