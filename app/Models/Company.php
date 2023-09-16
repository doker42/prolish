<?php

namespace App\Models;

use App\Domain\Company\CompanyManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Company extends Model
{
    use SoftDeletes, Billable;

    protected $fillable = [
        'title',
        'description',
        'parent_company',
        'logo',
        'status',
        'company_name',
        'company_number',
        'company_address',
        'company_account_number',
        'company_bank',
        'membership_id',
        'storage_used',
        'active_until',
        'owner_id',
        'verified',
        'founder',
        'linkedin',
        'facebook',
        'twitter',
        'website',
        'email',
        'phone',
        'office_address',
        'vat_number',
        'employees_number',
        'industry_id',
    ];

    protected $appends = [
        'header',
        'is_member',
        'can_delete'
    ];

    const VERIFICATION_REQUIRED_FIELDS = [
        'company_name',
        'company_number',
        'company_address',
        'registration_number',
    //    'company_account_number',
    //    'company_bank',
        'vat_number',
        'title',
    ];

    const COMPANY_VERIFIED = 1;
    const COMPANY_NOT_VERIFIED = 0;

    public function getVerificationFieldsListAttribute()
    {
        return self::VERIFICATION_REQUIRED_FIELDS;
    }

    public function getHeaderAttribute()
    {
        if ($this->attributes['logo'] && $this->attributes['logo'] != '/images/450x450.png') {
            return '<img src="' . $this->attributes['logo'] . '" />';
        }

        return $this->attributes['title'];
    }

    public function getEmployeesNumberValueAttribute()
    {
      return config('employee_numbers')[$this->attributes['employees_number']??1];
    }

    public function getIsMemberAttribute()
    {
        return Auth::check() && (Auth::user()->company_id == $this->attributes['id'] || Auth::user()->company_id == $this->attributes['owner_id']);
    }

    public function getCanDeleteAttribute()
    {
        if (Auth::check() && ((Auth::user()->role == 'super_user' && Auth::user()->id != $this->attributes['owner_id']
                    && $this->attributes['verified'] == self::COMPANY_NOT_VERIFIED && Carbon::now() > $this->attributes['active_until'])
                || (Auth::user()->id == $this->attributes['owner_id'] && Auth::user()->company_id != $this->attributes['id']))) {
            return true;
        }

        return false;
    }

    public function getWebdavUrlAttribute()
    {
//        return env('NEXTCLOUD_API_URL').'/remote.php/dav/files/prod_polish_'.$this->attributes['id'];
        return env('NEXTCLOUD_API_URL').'/remote.php/dav/files/admin2/';
    }

    public function getTemporaryFolderAttribute()
    {
        return 'prod_polish_'.$this->attributes['id'];
    }
    public function setActiveUntilAttribute($value)
    {
        $this->attributes['active_until'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function parent()
    {
        return $this->hasOne('App\Models\Company', 'id', 'parent_company');
    }

    public function owner()
    {
        return $this->hasOne('App\Models\User', 'id', 'owner_id');
    }

    public function industry()
    {
        return $this->hasOne(Industry::class, 'id', 'industry_id');
    }

    public function admins()
    {
        return $this->hasMany('App\Models\User', 'company_id')->where('role', 'administrator');
    }

    public function users()
    {
        return $this->hasMany('App\Models\User', 'company_id');
    }

    public function projects()
    {
        return $this->hasMany('App\Models\Project', 'company_id');
    }

    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'companies_specialization',  'company_id', 'specialization_id');
    }

    public function membership()
    {
        return $this->hasOne('App\Models\Membership', 'id', 'membership_id');
    }

    public static function manager(): CompanyManager
    {
        return CompanyManager::up();
    }
}
