<?php

namespace App\Models;

use App\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'status'
    ];

    protected $casts = [
        'status' => RegistrationStatus::class,
    ];
}
