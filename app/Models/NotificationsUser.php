<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationsUser extends Model
{
    protected $table = 'notifications_user';

    protected $fillable = [
        'user_id',
        'notification_id'
    ];
}
