<?php

namespace App\Http\Controllers;

use App\Jobs\CopyProject;
use App\Jobs\RecalculateCompany;
use App\Models\ActivityLog;
use App\Models\AdditionalUserCompanies;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\Project;
use App\Models\ProjectItem;
use App\Models\ProjectLog;
use App\Models\ProjectVisibility;
use App\Models\User;
use App\Models\UserFavouriteProject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\DB;
use App\Mail\InviteToProjectNotify;
use App\Mail\UserLeftEntity;
use App\Mail\EntityAccessDenied;

class ProjectController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return array
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $projects = [];
        $per_page = $request->get('per_page', 10);

        $roles = config('roles');

        $belong_to = $request->get('belong_to');
        $sort = $request->get('sort');
        $sort_order = $request->get('sort_order');

        $personal_roles = User::manager()->getUserRoles($user->id);

        $perosnal_visible = ProjectVisibility::where('user_id', $user->id)->get();

        $not_active_projects = DB::table('projects')->select('id', 'deleted_at')
            ->whereIn('company_id', DB::table('companies')->whereNotNull('deleted_at')->pluck('id')->toArray())
            ->orWhereIn('company_id', DB::table('companies')->where('verified', 0)->pluck('id')->toArray())
            ->orWhereIn('company_id', DB::table('companies')->whereNotNull('active_until')->where('active_until', '<', \Carbon\Carbon::now())->pluck('id')->toArray())
            ->pluck('id');

        if ($belong_to == 'favourite') {
            $favourites = UserFavouriteProject::where('user_id', Auth::user()->id)->pluck('project_id');

            if ($user->role != 'super_user') {

                $projects = Project::whereIn('id', $favourites->toArray())
                    ->whereNotIn('id', $not_active_projects)
                    ->where(function ($query) use ($request, $personal_roles) {
                        if ($request->get('query')) {
                            $query->whereIn('id', array_keys($personal_roles['project']));
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->with('items')
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);

                $projects->each(function ($item) use ($roles, $user, $perosnal_visible, $personal_roles) {

                    $item->permissions =  isset($personal_roles['project'][$item->id])? $roles[$personal_roles['project'][$item->id]]['permissions']:$user->role;

                    $pers_role = $perosnal_visible->where('project_id', $item->id)->first();
                    if (!empty($pers_role)) {
                        $item->personal_visibility = true;
                    }
                });

            } else {
                $projects = Project::whereIn('id', $favourites)
                    ->where(function($query) use ($request){
                        if ($request->get('query')) {
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->with('items')
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);
            }

        } else if ($belong_to == 'shared') {
            $projects = Project::whereIn('id', $personal_roles['shared_projects'])
                ->whereNotIn('id', $not_active_projects)
                ->where(function($query) use ($request){
                    if ($request->get('query')) {
                        $query->where('title', 'like', '%' . $request->get('query') . '%');
                    }
                })
                ->with('items')
                ->orderBy($sort, $sort_order)
                ->paginate($per_page);

            $projects->each(function ($item) use ($roles, $perosnal_visible, $personal_roles) {
                $item->permissions =  isset($personal_roles['project'][$item->id])? $roles[$personal_roles['project'][$item->id]]['permissions']:'visitor';
                $pers_role = $perosnal_visible->where('project_id', $item->id)->first();
                if(!empty($pers_role)){
                    $item->personal_visibility = true;
                }
            });
        } else if ($belong_to == 'all') {
            if ($user->role != 'super_user') {

                $projects = Project::whereIn('id', array_keys($personal_roles['project']))
                    ->where(function($query) use ($request){
                        if ($request->get('query')) {
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->whereNotIn('id', $not_active_projects)
                    ->with('items')
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);

                $projects->each(function ($item) use ($roles, $user, $perosnal_visible, $personal_roles) {
                    $item->permissions =  isset($personal_roles['project'][$item->id])? $roles[$personal_roles['project'][$item->id]]['permissions']:$user->role;

                    $pers_role = $perosnal_visible->where('project_id', $item->id)->first();
                    if(!empty($pers_role)){
                        $item->personal_visibility = true;
                    }
                });
            } else {
                $projects = Project::with('items')
                    ->where(function($query) use ($request){
                        if ($request->get('query')) {
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);
            }
        } else if ($belong_to == 'archived') {
            if ($user->role != 'super_user') {

                $projects = Project::onlyTrashed()
                    ->whereNotIn('id', $not_active_projects)
                    ->where(function($query) use ($request, $personal_roles){
                        $query->whereIn('company_id', array_keys($personal_roles['company']));
                        $query->orWhereIn('id', $personal_roles['shared_projects']);
                        if ($request->get('query')) {
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->with('items')
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);

                $projects->each(function ($item) use ($roles, $personal_roles, $user, $perosnal_visible) {

                    $item->permissions =  isset($personal_roles['project'][$item->id])? $roles[$personal_roles['project'][$item->id]]['permissions']:$user->role;

                    $pers_role = $perosnal_visible->where('project_id', $item->id)->first();
                    if(!empty($pers_role)){
                        $item->personal_visibility = true;
                    }
                });
            } else {
                $projects = Project::with('items')
                    ->where(function($query) use ($request){
                        if ($request->get('query')) {
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->onlyTrashed()
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);
            }
        } else {
            if ($user->role != 'super_user') {

                if (isset($personal_roles['company'][$belong_to])) {
                    $projects = Project::where('company_id', $belong_to)
                        ->whereIn('id', array_keys($personal_roles['project']))
                        ->whereNotIn('id', $not_active_projects)
                        ->where(function($query) use ($request, $belong_to, $user){
                            if ($request->get('query')) {
                                $query->where('title', 'like', '%' . $request->get('query') . '%');
                            }
                        })
                        ->with('items')
                        ->orderBy($sort, $sort_order)
                        ->paginate($per_page);

                    $projects->each(function ($item) use ($roles, $personal_roles, $user, $perosnal_visible) {
                        $item->permissions =  isset($personal_roles['project'][$item->id])? $roles[$personal_roles['project'][$item->id]]['permissions']:$user->role;

                        $pers_role = $perosnal_visible->where('project_id', $item->id)->first();
                        if(!empty($pers_role)){
                            $item->personal_visibility = true;
                        }
                    });
                } else {
                    return [];
                }
            } else {
                $projects = Project::where('company_id', $belong_to)
                    ->where(function($query) use ($request){
                        if ($request->get('query')) {
                            $query->where('title', 'like', '%' . $request->get('query') . '%');
                        }
                    })
                    ->with('items')
                    ->orderBy($sort, $sort_order)
                    ->paginate($per_page);
            }
        }

        if ($user->role == 'super_user') {
            $projects->each(function ($item) use ($roles) {
                $item->permissions = $roles['administrator']['permissions'];
            });
        }

        if ($request->get('json')) {
            $json = [
                'type' => 'FeatureCollection',
                'crs' => [
                    'type' => 'name',
                    'properties' => [
                        'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'
                    ]
                ],
                'features' => []
            ];

            $projects->each(function ($item) use (&$json) {
                if ($item->geo_point) {
                    $json['features'][] = [
                        'type' => 'Feature',
                        'properties' => [
                            'id' => $item->id,
                            'html' => '<div class="row">
                                        <div class="col-sm-6">
                                        <p class="font-weight-bold h6 mb-1">' . $item->title . '</p>
                                        <p class="small mb-1" style="line-height: 10px;">' . $item->address . '</p>
                                        <p>' . Str::words($item->description, 30) . '</p>
                                        </div>
                                        <div class="col-sm-6">
                                        <img src="' . $item->image . '" style="max-height: 100px;max-width:100%;" />'.(!empty($item->company)??'
                                        <br/>
                                        <img src="' . $item->company->logo .'" style="max-height: 20px;max-width: 100%" />' ).'
                                    </div>
                                   </div>'
                        ],
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [
                                $item->geo_point['lng'],
                                $item->geo_point['lat']
                            ]
                        ]
                    ];
                }
            });

            return $json;
        }

        // Log authorisation
        if (Auth::check()) {
            $time = Carbon::now()->subHour(1);
            $check = ActivityLog::where('created_at', '>', $time)->where('user_id', Auth::user()->id)->first();

            if (empty($check)) {
                $ip = $request->ip();
                $geoip = geoip($ip);

                ActivityLog::create([
                    'user_id' => Auth::user()->id,
                    'action' => 'usage',
                    'data' => [
                        'ip' => $ip,
                        'device' => Agent::browser() . ', ' . Agent::platform(),
                        'location' => $geoip->city . ', ' . $geoip->country
                    ]
                ]);
            }
        }

        return $projects;
    }
    /*
        * returns all manageable projects for the current user
        */
    public function manageableIndex()
    {
        $permissions = Session::get('user_roles');
        if (is_null($permissions)){
            $permissions = User::manager()->getUserRoles(Auth::user()->id);
        }
        if (isset($permissions['is_super_user']) && isset($permissions['is_super_user'])){
            $projects = Project::with('gallery_folders')->get();
        } else {
            $managable_project_ids = [];
            foreach($permissions['project'] as $id => $role){
                if (in_array($role, ['administrator', 'manager'])){
                    $managable_project_ids[] = $id;
                }
            }
            $projects = Project::whereIn('id', $managable_project_ids)->with('gallery_folders')->whereHas('company', function ($query) {
                $query->whereNull('active_until')->orWhere('active_until', '>', \Carbon\Carbon::now());
            })->get();
        }
        $data_to_return = [];
        foreach($projects as $project){
            $data_to_return[$project->id] = [
                'title' => $project->title,
                'folders' => [],
            ];
            foreach ($project->gallery_folders as $gallery_folder) {
                $data_to_return[$project->id]['folders'][$gallery_folder->id] = $gallery_folder->title;
            }
        }
        return $data_to_return;
    }

    public function public(Request $request)
    {
        $projects = Project::where('public', true)->with('items')->get();

        if ($request->get('json')) {
            $json = [
                'type' => 'FeatureCollection',
                'crs' => [
                    'type' => 'name',
                    'properties' => [
                        'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'
                    ]
                ],
                'features' => []
            ];

            $projects->each(function ($item) use (&$json) {
                if ($item->geo_point) {
                    $json['features'][] = [
                        'type' => 'Feature',
                        'properties' => [
                            'id' => $item->id,
                            'html' => '<div class="row">
                                        <div class="col-sm-6">
                                        <p class="font-weight-bold h6 mb-1">' . $item->title . '</p>
                                        <p class="small mb-1" style="line-height: 10px;">' . $item->address . '</p>
                                        <p>' . Str::words($item->description, 30) . '</p>
                                        </div>
                                        <div class="col-sm-6">
                                        <img src="' . $item->image . '" style="max-height: 100px;" />'.(!empty($item->company)??'
                                        <br/>
                                        <img src="' . $item->company->logo .'" style="max-height: 20px;max-width: 100%" />' ).'
                                    </div>
                                   </div>'
                        ],
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [
                                $item->geo_point['lng'],
                                $item->geo_point['lat']
                            ]
                        ]
                    ];
                }
            });

            return $json;
        }

        return $projects;
    }

    public function store(Request $request)
    {
        $project = Project::create($request->all());

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $project->id,
            'trans' => 'logs_created',
            'data' => []
        ]);

        return $project;
    }

    public function show($id)
    {
        $project = Project::where('id', $id)->with('items.potree', 'items.url', 'contacts')->first();
        $types = config('allowed_types');
        $items = [];

        foreach ($project->items as $item) {
            foreach ($types as $category => $type) {
                if (!empty($type[$item->type])) {
                    $items[$category][$item->type][] = $item;
                    break;
                }
            }
        }

        // Sorting
        foreach(array_keys($types) as $category){
            if (isset($items[$category])) {
                $array_order = array_keys($types[$category]);
                uksort($items[$category], function ($key1, $key2) use ($array_order) {
                    return (array_search($key1, $array_order) > array_search($key2, $array_order));
                });
            }
        }

        unset($project->items);
        $project->items = $items;
        $project->deleted = ProjectItem::where('project_id', $id)->onlyTrashed()->get();

        // Permissions
        $roles = config('roles');
        $user = Auth::user();

        if ($user->role == 'super_user') {
            $project->permissions = $roles['administrator']['permissions'];
        } else {
            $permissions = User::manager()->getUserRoles($user->id);
            if (!empty($permissions['project'][$id])){
                $project->permissions = $roles[$permissions['project'][$id]]['permissions'];
            } else {
                return response()->json(['errors'=>trans('custom.content_not_allowed')], 403);
            }
        }

        return $project;
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->update($request->all());

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $project->id,
            'trans' => 'logs_updated',
            'data' => [
                'fields' => $request->all()
            ]
        ]);

        return $project;
    }

    public function restore($id)
    {
        $item = Project::withTrashed()->where('id', $id)->first();

        $item->restore();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_restore',
            'data' => []
        ]);

        return ['message' => 'success'];
    }

    public function destroy($id)
    {
        Project::find($id)->delete();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_move_trash',
            'data' => []
        ]);

        return ['message' => 'success'];
    }

    public function forceDelete($id)
    {
        Project::manager()->deleteAndNotify($id);

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_delete',
            'data' => []
        ]);

        return ['message' => 'success'];
    }

    public function favourite(Request $request, $id)
    {
        $value = [
            'user_id' => Auth::user()->id,
            'project_id' => $id
        ];

        if ($request->get('favourite')) {
            UserFavouriteProject::firstOrCreate($value);
        } else {
            UserFavouriteProject::where($value)->delete();
        }

        return ['message' => 'success'];
    }

    public function visibility($id)
    {
        return ProjectVisibility::where('project_id', $id)->with('user', 'company')->get();
    }

    public function visibility_add(Request $request)
    {

        $id = $request->get('id');
        if ($request->get('type') == 'users') {
            foreach ($request->get('data') as $item) {
                if (!$item['role']) {
                    continue;
                }

                $user = User::where('email', $item['email'])->first();

                if (empty($user)) {

                     Invitation::create([
                        'email' => $item['email'],
                        'project_id' => $id,
                        'role' => $item['role'],
                    ]);

                    Mail::to($item['email'])->send(new InviteToProjectNotify([
                        'email' => $item['email'],
                        'project_title' => Project::find($id)->title,
                        'sender_name' => Auth::user()->name,
                        'sender_email' => Auth::user()->email,
                        'type' => 'new_user',
                        'project_id' => $id,
                        'lang' => 'en']));
                } else {

                    ProjectVisibility::where([
                        'project_id' => $id,
                        'user_id' => $user->id
                    ])->delete();

                    ProjectVisibility::create([
                        'project_id' => $id,
                        'user_id' => $user->id,
                        'role' => $item['role']
                    ]);

                    ProjectLog::create([
                        'user_id' => Auth::user()->id,
                        'project_id' => $id,
                        'trans' => 'logs_visibility_add',
                        'data' => [
                            'user_id' => $user->id,
                            'email' => $item['email'],
                            'role' => $item['role']
                        ]
                    ]);

                    Mail::to($item['email'])->send(new InviteToProjectNotify([
                        'email' => $item['email'],
                        'project_id' => $id,
                        'project_title' => Project::find($id)->title,
                        'sender_name' => Auth::user()->name,
                        'sender_email' => Auth::user()->email,
                        'role' => $item['role'],
                        'type' => 'existing_user',
                        'lang' => $user->locale]));
                }
            }
        } else {
            foreach ($request->get('data') as $item) {
                if (!$item['role']) {
                    continue;
                }

                ProjectVisibility::where([
                    'project_id' => $id,
                    'company_id' => $item['id']
                ])->delete();

                ProjectVisibility::create([
                    'project_id' => $id,
                    'company_id' => $item['id'],
                    'role' => $item['role']
                ]);
            }

            ProjectLog::create([
                'user_id' => Auth::user()->id,
                'project_id' => $id,
                'trans' => 'logs_visibility_add_company',
                'data' => [
                    'company_id' => $item['id'],
                    'email' => $item['title'],
                    'role' => $item['role']
                ]
            ]);
        }

        return ['message' => 'success'];
    }

    public function visibility_change(Request $request, $id)
    {
        ProjectVisibility::where([
            'project_id' => $id,
            'user_id' => $request->get('user_id'),
            'company_id' => $request->get('company_id')
        ])->update(['role' => $request->get('role')]);

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_visibility_edit',
            'data' => [
                'user_id' => $request->get('user_id'),
                'company_id' => $request->get('company_id'),
                'role' => $request->get('role')
            ]
        ]);

        return ['message' => 'success'];
    }

    public function leaveProject(Request $request, $id)
    {
        $user = Auth::user();

        $project = Project::find($id);

        ProjectVisibility::where([
            'project_id' => $id,
            'user_id' => Auth::user()->id,
        ])->delete();

        UserFavouriteProject::where([
            'project_id' => $id,
            'user_id' => Auth::user()->id,
        ])->delete();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_visibility_remove',
            'data' => [
                'user_id' => Auth::user()->id,
                'company_id' => $user->company_id,
            ]
        ]);

        $admins = User::whereIn('id', Company::manager()->getAllAdminsIds($project->company_id))->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new UserLeftEntity([
                'entity' => 'project',
                'project_name' => $project->title,
                'project_id' => $id,
                'user_name' => Auth::user()->name,
                'lang' => $admin->locale]));
        }

        User::manager()->notificate($admins, [
            'title' => 'attention__',
            'message' => 'user_left_the_project_notification',
            'addition_vars' => [
                'user_name' => $user->name,
                'project_name' => $project->title,
            ]
        ]);

        return [
            'message' => 'success',
            'description' =>  trans('custom.you_have_left_the_project', ['project_name' => $project->title, 'admin_email'=> $project->company->owner->email]),
        ];
    }

    public function visibility_delete(Request $request, $id)
    {
        $project = Project::find($id);
        $user = User::find($request->get('user_id'));

        ProjectVisibility::where([
            'project_id' => $id,
            'user_id' => $user->id,
            'company_id' => $request->get('company_id')
        ])->delete();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_visibility_remove',
            'data' => [
                'user_id' => $request->get('user_id'),
                'company_id' => $request->get('company_id')
            ]
        ]);

        Mail::to($user->email)->send(new EntityAccessDenied([
            'entity' => 'project',
            'project_name' => $project->company->title,
            'admin_email' => $project->company->owner->email,
            'user_name' => $user->name,
            'lang' => $user->locale]));

        return [
            'message' => 'success',
        ];
    }

    public function check_create()
    {
            $user = Auth::user();

            if (in_array($user->role, ['manager', 'administrator', 'super_user']) &&
                $user->company->deleted_at == null &&
                $user->company->verified == Company::COMPANY_VERIFIED &&
                (Carbon::parse($user->company->active_until)->gt(Carbon::now()) || $user->company->active_until == null)
               ){
                return 1;
            } else {
                $additional_companies = AdditionalUserCompanies::where('user_id', $user->id)->whereIn('role', ['administrator', 'manager'])->pluck('company_id');
                $add_companies = Company::whereIn('id', $additional_companies)->whereNull('deleted_at')->where('verified', 1)->where(function($query){
                    $query->whereNull('active_until');
                    $query->orWhere('active_until', '>', Carbon::now());
                })->get();
                if ($add_companies->count() > 0){
                    return 1;
                }
            }

        return 0;
    }

    public function emails()
    {
        $user = Auth()->user();

        if ($user->role == 'super_user') {
            return User::where('id', '!=', Auth::user()->id)->get(['email', 'picture', 'name']);
        }

        $additional_companies = AdditionalUserCompanies::where('user_id', $user->id)->get();

        $companies = Company::where(function ($query) use ($user){
                // Limit viewability
                if ($user->role == 'administrator') {
                    $query->where('parent_company', $user->company_id);
                }
            })
            ->orWhere('owner_id', $user->id)
            ->orWhere('id', $user->company_id)
            ->orWhereIn('id', $additional_companies->pluck('company_id'))
            ->pluck('id');

        $projects = Project::whereIn('company_id', $companies)->pluck('id');

        $visibility = ProjectVisibility::whereIn('project_id', $projects)->pluck('user_id');

        return User::whereIn('id', $visibility)->where('id', '!=', Auth::user()->id)->get(['email', 'picture', 'name']);
    }

    public function visibilityCounter(Request $request) {
        $validator = Validator::make($request->all(), [
            'ids' => 'array|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        $ids = $request->get('ids');

        $visible = ProjectVisibility::whereIn('project_id', $ids)->get();

        $output = [];

        foreach ($ids as $id) {
            $output[$id] = $visible->where('project_id', $id)->count();
        }

        return $output;
    }
}
