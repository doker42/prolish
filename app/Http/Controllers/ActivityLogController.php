<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $role = Auth::user()->role;

        if ($role == 'administrator' || $role == 'super_user') {
            $to = Carbon::parse($request->get('to'))->endOfDay();
            $from = Carbon::parse($request->get('from'))->startOfDay();

            $log = ActivityLog::whereBetween('created_at', [$from, $to]);

            if ($user = $request->get('user')) {
                $log = $log->where('user_id', $user);
            }

            $log = $log->get();

            $grouped = $log->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            });

            $dates = $this->generateDateRange($from, $to);
            $chart = [['Date', 'Users']];

            foreach ($dates as $date) {
                $chart[] = [$date, empty($grouped[$date]) ? 0 : count($grouped[$date])];
            }

            return [
                'log' => $log,
                'chart' => $chart
            ];
        }

        return [];
    }

    private function generateDateRange(Carbon $start_date, Carbon $end_date)
    {
        $dates = [];

        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    public function numbers()
    {
        $companies = Company::count();
        $projects = Project::count();
        $income = Invoice::where('payment_status', '!=', 'Invalid')->sum('price');

        return [
            'companies' => $companies,
            'projects' => $projects,
            'income' => number_format($income, 2, ".", "")
        ];
    }
}
