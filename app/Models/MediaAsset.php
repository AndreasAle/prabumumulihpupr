<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    protected $guarded = [];

    protected $casts = ['published_at' => 'datetime'];

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
