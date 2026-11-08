<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $guarded = [];

    protected $casts = ['start_datetime' => 'datetime', 'end_datetime' => 'datetime', 'scheduled_at' => 'datetime'];
}
