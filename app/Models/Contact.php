<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'fullname',
        'phone',
        'email',
        'position',
        'project_id'
    ];

    protected $appends = [
        'picture'
    ];

    public function getPictureAttribute() {
        return User::where('email', $this->attributes['email'])->pluck('picture')[0] ?? null;
    }

}
