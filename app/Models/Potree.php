<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Potree extends Model
{
    protected $table = 'potree';

    protected $fillable = [
        'item_id',
        'filename'
    ];

    protected $appends = [
        'url'
    ];

    public function getUrlAttribute()
    {
        return '/potree/' . $this->attributes['id'];
    }

    public function getPointcloudAttribute()
    {
        return '/potree/pointclouds/' . $this->attributes['filename'];

    }

    public function getFilenameAttribute($value)
    {
        return '/potree/pointclouds/' . $value . '/cloud.js';
    }
}
