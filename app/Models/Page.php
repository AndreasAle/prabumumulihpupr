<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    protected $casts = ['published_at' => 'datetime', 'scheduled_at' => 'datetime'];
}
