<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationsUnseen;
use App\Models\NotificationsUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
       // NotificationsUnseen::where('user_id', Auth::user()->id)->delete();

        $users_notifications = NotificationsUser::where('user_id', Auth::user()->id)->pluck('notification_id');


        $system_notifications = Notification::whereNotIn('id', NotificationsUser::groupBy('notification_id')->pluck('notification_id'))->pluck('id');

        return Notification::whereIn('id', $users_notifications)->orWhereIn('id', $system_notifications)->orderBy('id', 'DESC')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $notification = Notification::create($request->only([
            'title_en',
            'title_ru',
            'title_lv',
            'title_et',
            'content_en',
            'content_ru',
            'content_lv',
            'content_et'
        ]));

        User::all()->each(function ($user) use ($notification) {
            NotificationsUnseen::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id
            ]);
        });

        return $notification;
    }

    /**
     * Display the specified resource.
     *
     */
    public function show($id)
    {
        return Notification::find($id) ?? [];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     */
    public function update($id, Request $request)
    {
        $notification = Notification::find($id);

        $notification->update($request->only([
            'title_en',
            'title_ru',
            'title_lv',
            'title_et',
            'content_en',
            'content_ru',
            'content_lv',
            'content_et'
        ]));

        return $notification;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     */
    public function destroy($id)
    {
        NotificationsUnseen::where('notification_id', $id)->delete();
        Notification::find($id)->delete();

        return ['message' => 'success'];
    }
}
