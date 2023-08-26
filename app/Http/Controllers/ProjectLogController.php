<?php

namespace App\Http\Controllers;

use App\Models\ProjectLog;
use Illuminate\Support\Facades\Auth;

class ProjectLogController extends Controller
{
    public function index($id)
    {
        $role = Auth::user()->role;

        if ($role == 'administrator' || $role == 'super_user') {
            return ProjectLog::where('project_id', $id)->get();
        }

        return [];
    }
}
