<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_specializations', 'specialization_id', 'company_id');
    }
}
