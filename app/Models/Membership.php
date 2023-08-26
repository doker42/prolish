<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    const FREE_PACKAGE_ID = 1;

    protected $fillable = [
        'title',
        'price',
        'type',
        'size'
    ];

    public function getSizeAttribute()
    {
        return number_format($this->attributes['size'], 2, ".", "");
    }

    public function getSupportTypeAttribute()
    {
        return config('support_types')[$this->attributes['support_type']??1];
    }
    public function getSOverlimitGbPriceAttribute()
    {
        return number_format($this->attributes['overlimit_gb_price'], 2, ".", "");
    }
    public function getManagersLimitAttribute()
    {
        return $this->attributes['managers_limit'] == 0?trans('custom.unlimited', []):$this->attributes['managers_limit'];
    }
    public function getProjectsLimitAttribute()
    {
        return $this->attributes['projects_limit'] == 0?trans('custom.unlimited', []):$this->attributes['projects_limit'];
    }
    public function getVisitorsLimitAttribute()
    {
        return $this->attributes['visitors_limit'] == 0?trans('custom.unlimited', []):$this->attributes['visitors_limit'];
    }
}
