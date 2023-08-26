<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalUserCompanies extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'role'
    ];
}
