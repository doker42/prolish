<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectVisibility extends Model
{
    protected $fillable = [
        'project_id',
        'role',
        'user_id',
        'company_id'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }
}
