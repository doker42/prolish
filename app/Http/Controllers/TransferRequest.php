<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProceedTransfer;
use App\Jobs\RecalculateCompany;
use App\Models\Company;
use App\Models\Membership;
use App\Models\ProjectVisibility;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\TransferRequest as Transfer;
use Illuminate\Http\JsonResponse;

class TransferRequest extends Controller
{

    /**
     * Show the list of available transfer requests.
     *
     * @return array
     */
    public function index()
    {
       $user_roles = User::manager()->getUserRoles(Auth::user()->id);
       $admin_roled_companies = [];
       foreach($user_roles['company'] as $id=>$role){
           if($role == 'administrator'){
               $admin_roled_companies[] = $id;
           }
       }
       return Transfer::whereIn('company_id', $admin_roled_companies)->where('is_processing', 0)->with('project')->get();

    }

    /* Creates Transfer Request
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function store(Request $request):JsonResponse
    {
        $result = true;

        try{
            $project = Project::where('id', $request->get('id'))->first();

            $old_company = Company::find($project->company_id);

            if(empty($project)){
                throw new \Exception('Can\'t find Project for Transfer');
            }

            $company = Company::find($request->get('company_id'));

            if(empty($company)){
                throw new \Exception('Can\'t find Company for Transfer');
            }

            $type = $request->get('type');

            if (!in_array($type,config('transfer_types'))){
                throw new \Exception('Transfer type is not supported');
            }

            Transfer::where([
                'company_id' => $company->id,
                'project_id' => $project->id,
            ])->delete();

            Transfer::create([
                'company_id' => $company->id,
                'project_id' => $project->id,
                'type' => array_search($type,config('transfer_types'))
            ]);

            $new_admins = Company::manager()->getAllAdminsIds($company->id);
            $users = User::whereIn('id', $new_admins)->get();
            User::manager()->notificate($users, [
                'title' => 'transfer_request',
                'message' => 'transfer_project_request_notification',
                'addition_vars' => [
                    'old_owner_company' => $old_company->title,
                    'project_name' => $project->title,
                    'new_owner_company' => $company->title,
                    'user_transfered' => Auth::user()->name,
                ]
            ]);


        } catch(\Exception $e){
            $result = false;
            $description = $e->getMessage();
        }

        return response()->json([
            'result' => $result,
            'description' => $description??'',
        ]);
    }

    /* Processes Transfer Request
   *
   * @return Response
   */
    public function process(Request $request):JsonResponse
    {
        $result = true;


        try{

            $transfer_request = Transfer::find($request->get('id'));

            if (empty($transfer_request)){
                throw new \Exception('This Transfer Request is no longer available');
            }

            $project = Project::find($transfer_request->project_id);
            $company = Company::find($transfer_request->company_id);

            if(empty($project) || empty($company)){
                throw new \Exception('This Transfer Request is no longer available');
            }

            $old_company = Company::find($project->company_id);


            if ($request->get('action') == 'approve') {

                $size = $company->storage_used ?? 0;
                $size += $project->size;

                $membership_size = 0;
                if ($membership = Membership::find($company->membership_id)) {
                    $membership_size = $membership->size ?? 0;
                }

                if($size > $membership_size){
                    throw new \Exception(trans('custom.not_enough_space_to_transfer'));
                }

                if ($transfer_request->type == Transfer::TYPE_COPYING) {
                    ProceedTransfer::dispatch($transfer_request->id, Auth::user()->id);
                    $transfer_request->is_processing = 1;
                    $transfer_request->save();
                }
                if ($transfer_request->type == Transfer::TYPE_CHANGE_OWN) {
                    $project->company_id = $company->id;
                    $project->save();
                    ProjectVisibility::where('project_id', $project->id)->delete();
                    RecalculateCompany::dispatch($company);
                    RecalculateCompany::dispatch($old_company);
                    $old_admins = Company::manager()->getAllAdminsIds($old_company->id);
                    $new_admins = Company::manager()->getAllAdminsIds($company->id);
                    $users = User::whereIn('id', $old_admins)->orWhereIn('id', $new_admins)->get();
                    User::manager()->notificate($users, [
                        'title' => 'transfer_succeed',
                        'message' => 'transfer_project_approved_notification',
                        'addition_vars' => [
                            'old_owner_company' => $old_company->title,
                            'project_name' => $project->title,
                            'new_owner_company' => $company->title,
                            'user_approved' => Auth::user()->name,
                        ]
                    ]);
                    $transfer_request->delete();
                }
            }

            if ($request->get('action') == 'decline') {
                $old_admins = Company::manager()->getAllAdminsIds($old_company->id);
                $new_admins = Company::manager()->getAllAdminsIds($company->id);
                $users = User::whereIn('id', $old_admins)->orWhereIn('id', $new_admins)->get();
                User::manager()->notificate($users, [
                    'title' => 'transfer_declined',
                    'message' => 'transfer_project_declined_notification',
                    'addition_vars' => [
                        'old_owner_company' => $old_company->title,
                        'project_name' => $project->title,
                        'new_owner_company' => $company->title,
                        'user_declined' => Auth::user()->name,
                    ]
                ]);
                $transfer_request->delete();
            }

        } catch(\Exception $e){
            $result = false;
            $trace = $e->getTrace();
            $description = $e->getMessage();
        }

        return response()->json([
            'result' => $result,
            'trace' => $trace??'',
            'description' => $description??'',
        ]);
    }


}