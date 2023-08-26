<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    protected $appends = [
        'user',
        'device',
        'ip'
    ];

    public function getUserAttribute()
    {
        if ($user = User::find($this->attributes['user_id'])) {
            return $user->name . ' (' . $user->email . ')';
        }

        return 'DELETED';
    }

    public function getDeviceAttribute()
    {
        $data = json_decode($this->attributes['data']);

        return $data->device ?? '';
    }

    public function getIpAttribute()
    {
        $data = json_decode($this->attributes['data']);

        return ($data->ip) ? $data->ip . ' (' . $data->location . ')' : '';
    }
}
