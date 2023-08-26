<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLang($lang)
    {
        if (array_key_exists($lang, Config::get('languages'))) {
            Session::put('applocale', $lang);
            if (Auth::check()) {
                $user = Auth::user();
                $settings = $user->settings;
                if (empty($settings)) {
                    $settings = UserSettings::create([
                        'user_id' => $user->id,
                    ]);
                }
                $data = $settings->data;
                $data['locale'] = $lang;
                $settings->data = $data;
                $settings->save();
            }
        }
        return Redirect::back();
    }
}