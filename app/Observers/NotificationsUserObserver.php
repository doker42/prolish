<?php
declare(strict_types=1);

namespace App\Observers;


use App\Models\NotificationsUnseen;
use App\Models\NotificationsUser;

class NotificationsUserObserver
{
    /**
     * Handle the NotificationsUser "created" event.
     *
     * @param \App\Models\NotificationsUser $notificationsUser
     * @return void
     */
    public function created(NotificationsUser $notificationsUser)
    {
        NotificationsUnseen::create([
            'user_id' => $notificationsUser->user_id,
            'notification_id' => $notificationsUser->notification_id
        ]);
    }
}