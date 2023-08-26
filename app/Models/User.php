<?php

namespace App\Models;

use App\Domain\User\UserManager;
use App\Mail\ResetPasswordEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\PasswordReset;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    const VERIFIED_NOT_VERIFIED = 0;
    const VERIFIED_VERIFIED = 1;
    const VERIFIED_AWAITS_APPROVE = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'created_at',
        'picture',
        'phone',
        'jon_title',
        'verified',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $appends = [
        'company',
    ];

    public function getPictureAttribute($value)
    {
        return $value ?? '/images/user_pick_default.png';
    }

    public function getLocaleAttribute(){
        $locale = 'en';
        $settings = UserSettings::where('user_id', $this->attributes['id'])->first();
        if (!empty($settings)){
            $data = $settings->data;
            if (isset($data['locale'])){
                $locale = $data['locale'];
            }
        }
        return $locale;
    }

    public function getCompanyAttribute()
    {
        if (!empty($this->attributes['company_id'])) {
            return Company::find($this->attributes['company_id']);
        }
    }

    public function verifyUser()
    {
        return $this->hasOne('App\Models\VerifyUser');
    }

    public function verifyNewEmail()
    {
        return $this->hasOne('App\Models\VerifyNewEmail');
    }

    public function settings()
    {
        return $this->hasOne('App\Models\UserSettings');
    }

    public function ownedCompanies()
    {
        return $this->hasMany('App\Models\Company', 'owner_id', 'id');
    }

    public function belongToCompany()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }


    public static function manager(): UserManager
    {
        return UserManager::up();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $locale = 'en';
        $settings = UserSettings::where('user_id', $this->attributes['id'])->first();
        if (!empty($settings)){
            $data = $settings->data;
            if (isset($data['locale'])){
                $locale = $data['locale'];
            }
        }

        Mail::to($this->attributes['email'])->send(new ResetPasswordEmail($token, $locale));
    }
}
