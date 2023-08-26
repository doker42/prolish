<?php

namespace App\Http\Controllers;

use App\Helpers\GDriveUtils;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function show()
    {
        return view('home');
    }

    public function storage()
    {
        $company = Company::find(Auth::user()->company_id);

        if (empty($company)) {
            return [
                'used' => 0,
                'total' => 0
            ];
        }

        return [
            'used' => number_format($company->storage_used / 1000, 2, ".", ""),
            'total' => $company->membership->size / 1000,
            'active_until' => $company->active_until,
            'is_owner' => Auth::user()->company->owner_id == Auth::user()->id,
            'title' => $company->membership->title,
        ];
    }

    public function test()
    {
        return GDriveUtils::getId('https://drive.google.com/open?id=ttt');
    }
}
