<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected $casts = ['requirements' => 'array', 'steps' => 'array', 'is_published' => 'boolean', 'scheduled_at' => 'datetime'];
}
