<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyNewEmail extends Model
{
    protected $fillable = [
        'token',
        'email',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
