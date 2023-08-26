<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLog extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'trans',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    protected $appends = [
        'text'
    ];

    public function getTextAttribute()
    {
        $data = json_decode($this->attributes['data'], true);

        if (!empty($data['fields'])) {
            $data['fields'] = json_encode($data['fields']);
        }

        $user = User::find($this->attributes['user_id']);

        if (!empty($user->name)) {
            $data['user'] = $user->name . ' (' . $user->email . ')';
        } else {
            $data['user'] = $user->email;
        }

        return '[' . $this->attributes['created_at'] . '] ' . trans('custom.' . $this->attributes['trans'], $data);
    }
}
