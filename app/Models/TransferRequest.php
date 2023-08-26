<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferRequest extends Model
{
    const TYPE_COPYING = 2;
    const TYPE_CHANGE_OWN = 1;

    protected $fillable = [
        'project_id',
        'company_id',
        'type',
        'is_processing',
    ];

    protected $appends = [
        'cast_type'
    ];

    public function getCastTypeAttribute($key)
    {
        return config('transfer_types')[$this->attributes['type']];
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id','project_id');
    }
}
