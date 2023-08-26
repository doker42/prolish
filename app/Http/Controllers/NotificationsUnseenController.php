<?php

namespace App\Http\Controllers;

use App\Models\NotificationsUnseen;
use Illuminate\Support\Facades\Auth;

class NotificationsUnseenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return NotificationsUnseen::where('user_id', Auth::user()->id)->get();
    }

    /**
     * Remove All unseen notifications for user from storage.
     *
     */
    public function deleteUnseen()
    {
        NotificationsUnseen::where('user_id', Auth::user()->id)->delete();

        return ['message' => 'success'];
    }
}
