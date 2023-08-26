<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class Notification extends Model
{
    protected $fillable = [
        'title_en',
        'title_ru',
        'title_lv',
        'title_et',
        'content_en',
        'content_ru',
        'content_lv',
        'content_et'
    ];

    protected $appends = [
        'date',
        'unread',
    ];

    public function getDateAttribute()
    {
        $date = Carbon::parse($this->attributes['updated_at']);
        if ($date->isToday()) {
            return 'today';
        } elseif ($date->isYesterday()) {
            return 'yesterday';
        } else {
            return $date->format('d M y');
        }

    }
    public function getUnreadAttribute(){
        return NotificationsUnseen::where('notification_id', $this->attributes['id'])->where('user_id', Auth::user()->id)->count() != 0;
    }

}
