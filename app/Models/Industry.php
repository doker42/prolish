<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Industry extends Model
{
    protected $fillable = [
        'code',
    ];

    protected $appends = [
        'title'
    ];

    public function getTitleAttribute() {

        $user = Auth::user();

        return trans('custom.industry_values.' . $this->attributes['code'], [], $user->locale);
    }
}
