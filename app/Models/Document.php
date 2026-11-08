<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];

    protected $casts = ['published_at' => 'datetime', 'is_public' => 'boolean'];
}
