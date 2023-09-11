<?php

namespace App\Http\Controllers;

use App\Helpers\GDriveUtils;
use App\Jobs\CloudCompare;
use App\Jobs\ProcessItems;
use App\Models\Company;
use App\Models\ItemUrl;
use App\Models\Membership;
use App\Models\Project;
use App\Models\ProjectItem;
use App\Models\ProjectLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required',
            'type' => 'required',
            'urls' => 'required_without:upload|array|nullable',
            'urls.*' => 'url',
            'upload' => 'required_without:urls|array|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        $item = ProjectItem::create($request->only([
            'project_id',
            'title',
            'description',
            'type',
            'job_done_at'
        ]));

        $item->status = 'custom.item_status_success';

        if ($files = $request->get('upload')) {
            $potree_item_url = false;
            $cc_item_url = false;

            foreach ($files as $file) {
                list($gid, $extension) = $this->getFileExtension($file);

                $item_url = ItemUrl::create([
                    'item_id' => $item->id,
                    'url' => $file,
                    'type' => $extension
                ]);

                switch ($extension) {
                    case 'pdf':
                        $item->view_url = $file;
                        break;
                    case '3ds':
                    case 'obj':
                    case 'stl':
                    case 'off':
                        $item->view_url = '/3d-viewer/#/' . $file;
                        break;
                    case 'laz':
                    case 'las':
                        $potree_item_url = $item_url;
                        break;
                    case 'ptx':
                    case 'pts':
                    case 'xyz':
                    case 'e57':
                        $cc_item_url = $item_url;
                        break;
                }
            }

            if ($potree_item_url || $cc_item_url) {
                $item->status = 'custom.item_status_queue';

                if (!$potree_item_url && $cc_item_url) {
                    if (env("CC_SUPPORT")) {
                        CloudCompare::dispatch($cc_item_url, 'las', true, $gid);
                    }
                } else {
                    ProcessItems::dispatch($potree_item_url, $gid);
                }
            }
        }

        if ($request->get('urls')) {
            foreach ($request->get('urls') as $type => $url) {

                list($gid, $extension) = $this->getFileExtension($url);

                if ($type === 'youtube') {
                    if ($this->checkIfVideoUrl($url)) {
                        $extension = $type;
                    } else {
                        $item->forceDelete();
                        return response()->json(['errors'=>trans('validation.url', ['attribute' => 'url'])], 401);
                    }
                } else {
                    if ($extension == '') {
                        $item->forceDelete();
                        return response()->json(['errors' => trans('validation.url', ['attribute' => 'url'])], 401);
                    }
                }


                ItemUrl::create([
                    'item_id' => $item->id,
                    'url' => $url,
                    'type' => $extension
                ]);

                if ($type == 'view_url') {
                    $item->view_url = $url;
                }
            }
        }

        $item->save();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $request->get('project_id'),
            'trans' => 'logs_item_added',
            'data' => [
                'fields' => $request->only([
                    'title',
                    'type',
                    'urls'
                ])
            ]
        ]);

        return $item;
    }

    private function checkIfVideoUrl($url){
        if (preg_match('/youtube\.com/i', $url) || preg_match('/vimeo\.com/i', $url)){
            return true;
        }
        return false;
    }
    /*
        * Returns filtered items for a current user by a selected order
        */
    public function index(Request $request)
    {
        $order_field = $request->get('order_field', 'created_at');
        $order_dir = $request->get('order_dir', 'ASC');
        $per_page = $request->get('per_page', false);
        $tab = $request->get('tab', false);
        $type = $request->get('type', false);
        $uploader = $request->get('uploader', false);
        $project_filter = $request->get('belongs', false);

        $permissions = Session::get('user_roles');
        if (is_null($permissions)){
            $permissions = User::manager()->getUserRoles(Auth::user()->id);
        }
        if (isset($permissions['is_super_user']) && isset($permissions['is_super_user'])) {
            $items = ProjectItem::where(function ($query) use ($request, $project_filter, $tab, $type, $uploader) {
                if ($request->get('query')) {
                    $query->where('title', 'like', '%' . $request->get('query') . '%');
                }
                if ($project_filter && $project_filter != 'all') {
                    $query->where('project_id', $project_filter);
                }
                if ($type && $type != 'all') {
                    $query->where('type', $type);
                }
                if ($uploader && $uploader != 'all') {
                    $query->where('user_id', $uploader);
                }
                if ($tab == 'google_disk') {
                    $query->where('view_url', 'like', 'https://docs.google.com/document%');
                } else {
                    $query->where('view_url', 'not like', 'https://docs.google.com/document%')
                        ->orWhereNull('view_url');
                }
            })->with('project')->with('url')->with('uploader')->orderBy($order_field, $order_dir);

            if ($per_page) {
                return $items->paginate($per_page);
            } else {
                return $items->paginate($items->count());
            }
        } else {
            $managable_project_ids = [];
            foreach ($permissions['project'] as $id => $role) {
                if (in_array($role, ['administrator', 'manager'])) {
                    $managable_project_ids[] = $id;
                }
            }
            $managable_project_ids = Project::whereIn('id', $managable_project_ids)->whereHas('company', function ($query) {
                $query->whereNull('active_until')->orWhere('active_until', '>', \Carbon\Carbon::now());
            })->pluck('id');

            $items = ProjectItem::whereIn('project_id', $managable_project_ids)->where(function ($query) use ($request, $project_filter, $tab, $type, $uploader) {
                if ($request->get('query')) {
                    $query->where('title', 'like', '%' . $request->get('query') . '%');
                }
                if ($project_filter && $project_filter != 'all') {
                    $query->where('project_id', $project_filter);
                }
                if ($type && $type != 'all') {
                    $query->where('type', $type);
                }
                if ($uploader && $uploader != 'all') {
                    $query->where('user_id', $uploader);
                }
                if ($tab == 'google_disk') {
                    $query->where('view_url', 'like', 'https://docs.google.com/document%');
                }
            })->with('project')->with('url')->with('uploader')->orderBy($order_field, $order_dir);

            if ($per_page) {
                return $items->paginate($per_page);
            } else {
                return $items->paginate($items->count());
            }
        }
    }
    private function getFileExtension($url) {
        if (preg_match('/google\.com/i', $url)) {
            if (GDriveUtils::getType($url) == GDriveUtils::GOOGLE_DOC_TYPE){
                $extension = 'doc';
            } elseif(GDriveUtils::getType($url) == GDriveUtils::GOOGLE_SPREADSHEET_TYPE){
                $extension = 'exl';
            } elseif(GDriveUtils::getType($url) == GDriveUtils::GOOGLE_FILE_TYPE && !empty($gid = GDriveUtils::getId($url))) {
                $extension = GDriveUtils::getExt($gid);
            } else {
                $extension = '';
            }
        } else {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
        }

        return array($gid ?? null, $extension);
    }

    private function canUpload($files, $project_id)
    {
        $project = Project::find($project_id);
        $company = Company::find($project->company_id);

        $size = $company->storage_used ?? 0;

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        $membership = Membership::find($company->membership_id);
        $membership_size = $membership->size*1024*1024 ?? 0;

        return ($size > $membership_size) ? false : true;
    }
    /*
   * Returns all uploaders for a current user
   */
    public function uploadersIndex(Request $request)
    {
        $permissions = Session::get('user_roles');
        if (is_null($permissions)){
            $permissions = User::manager()->getUserRoles(Auth::user()->id);
        }
        if (isset($permissions['is_super_user']) && isset($permissions['is_super_user'])) {
//            dd(ProjectItem::get()->pluck('title'));
            exit();
            return ProjectItem::with('uploader')->get()->pluck('user_id','uploader.email');
        } else {
            $managable_project_ids = [];
            foreach ($permissions['project'] as $id => $role) {
                if (in_array($role, ['administrator', 'manager'])) {
                    $managable_project_ids[] = $id;
                }
            }
            return ProjectItem::whereIn('project_id', $managable_project_ids)->with('uploader')->get()->pluck('user_id','uploader.email');
        }
    }


    /*
       * Returns stream to download file
       *
       * @param  $code
        */
    public function externalDownload(string $code)
    {

        $url = Crypt::decryptString($code);

        if (file_exists(public_path($url))) {
            $filename = pathinfo($url, PATHINFO_FILENAME).'.'.pathinfo($url, PATHINFO_EXTENSION);
            // Send Download
            return Response::download(public_path($url), $filename, [
                'Content-Length: ' . filesize(public_path($url))
            ]);
        } else {
            // Error
            exit('Requested file does not exist on our server!');
        }

    }
    /*
    * Opens public view page for the item
    *
    * @param  $code
     */
    public function externalView(string $code)
    {
        $id = Crypt::decryptString($code);
        $item = ProjectItem::find($id);
        if (!empty($item)) {
            if ($item->type == 'point_clouds' && strpos($item->view_url, 'potree') > 0) {
                $items = ProjectItem::where('project_id', $item->id)->where('id', '!=', $item->id)->with('potree')->get();
                return view('potree', [
                    'main' => $item,
                    'sub' => $items,
                    'frame_view' => true,
                ]);
            }
            if (strpos($item->view_url, '3d-viewer/#/') > 0) {
                return view('3dview', [
                    'file' => str_replace('/3d-viewer/#', '', $item->view_url),
                ]);
            }
        } else {
            return redirect('/login');
        }
    }
    public function show($project_id, $id)
    {
        return ProjectItem::where('id', $id)->with('url')->first();
    }

    public function update(Request $request, $project_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required',
            'urls' => 'required_without:upload|array',
            'upload' => 'required_without:urls|array|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        $item = ProjectItem::findOrFail($id);
        $ignore_urls = [];

        if ($files = $request->get('upload')) {
            $potree_item_url = false;
            $cc_item_url = false;

            foreach ($files as $file) {
                list($gid, $extension) = $this->getFileExtension($file);

                // Delete old file
                $old_item = ItemUrl::where(['item_id' => $item->id, 'type' => $extension])->first();
                if (!empty($old_item)) {
                    if (file_exists(public_path($old_item->url))) {
                        unlink(public_path($old_item->url));
                    }
                    if ($old_item->type == 'laz' || $old_item->type == 'las') {
                        ItemUrl::manager()->deletePotreeFiles($old_item->id);
                    }
                    $old_item->delete();
                }

                // Ignore urls change
                $ignore_urls[] = $extension;

                $item_url = ItemUrl::create([
                    'item_id' => $item->id,
                    'url' => $file,
                    'type' => $extension
                ]);

                switch ($extension) {
                    case 'pdf':
                        $item->view_url = $file;
                        break;
                    case '3ds':
                    case 'obj':
                    case 'stl':
                    case 'off':
                        $item->view_url = '/3d-viewer/#/' . $file;
                        break;
                    case 'laz':
                    case 'las':
                        $potree_item_url = $item_url;
                        break;
                    case 'ptx':
                    case 'pts':
                    case 'xyz':
                    case 'e57':
                        $cc_item_url = $item_url;
                        break;

                }
            }

            if ($potree_item_url || $cc_item_url) {
                $item->status = 'custom.item_status_queue';

                if (!$potree_item_url && $cc_item_url) {
                    if (env("CC_SUPPORT")) {
                        CloudCompare::dispatch($cc_item_url, 'las', true, $gid);
                    }
                } else {
                    ProcessItems::dispatch($potree_item_url, $gid);
                }
            }
        }

        $item->update($request->only('title', 'description', 'job_done_at'));

        if ($request->get('urls')) {
            foreach ($request->get('urls') as $type => $url) {

                list($gid, $extension) = $this->getFileExtension($url);

                if ($type === 'youtube') {
                    if ($this->checkIfVideoUrl($url)) {
                        $extension = $type;
                    } else {
                        return response()->json(['errors'=>trans('validation.url', ['attribute' => 'url'])], 401);
                    }
                } else {
                    if ($extension == '') {
                        return response()->json(['errors' => trans('validation.url', ['attribute' => 'url'])], 401);
                    }
                }

                $item_url = ItemUrl::where(['item_id' => $item->id, 'type' => $extension])->first();

                if (in_array($type, $ignore_urls) || ($item_url && $item_url->url == $url)) {
                    continue;
                }

                if (!filter_var($url, FILTER_VALIDATE_URL) && !empty($url)) {
                    return response()->json(['errors'=>trans('validation.url', ['attribute' => 'url'])], 401);
                }

                ItemUrl::where(['item_id' => $item->id, 'type' => $extension])->delete();

                if (!empty($url)) {

                    ItemUrl::create([
                        'item_id' => $item->id,
                        'url' => $url,
                        'type' => $extension
                    ]);
                }

                if ($type == 'view_url') {
                    $item->view_url = $url;
                }
            }
        }

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $request->get('project_id'),
            'trans' => 'logs_item_updated',
            'data' => [
                'fields' => $request->only([
                    'title',
                    'type',
                    'urls'
                ])
            ]
        ]);

        return $item;
    }

    public function destroy($project_id, $id)
    {
        $item = ProjectItem::find($id);

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $item->project_id,
            'trans' => 'logs_item_deleted',
            'data' => [
                'title' => $item->title
            ]
        ]);

        $item->delete();

        return ['message' => 'success'];
    }

    public function restore($project_id, $id)
    {
        $item = ProjectItem::withTrashed()->where('id', $id)->first();

        $item->restore();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $project_id,
            'trans' => 'logs_item_restored_deleted',
            'data' => [
                'title' => $item->title
            ]
        ]);

        return ['message' => 'success'];
    }

    public function forceDelete($project_id, $id)
    {
        ProjectItem::manager()->delete($id);

        return ['message' => 'success'];
    }

    public function convert(Request $request, $project_id, $id)
    {
        $item_url = ItemUrl::where('item_id', $id)->whereIn('type', [
            'e57',
            'pts',
            'ptx',
            'xyz'
        ])->first();

        if (!empty($item_url) && $request->get('format')) {
            ProjectItem::where('id', $id)->update([
                'status' => 'custom.item_status_queue'
            ]);

            $make_potree = $request->get('format') == 'las' ? true : false;

            CloudCompare::dispatch($item_url, $request->get('format'), $make_potree);
        }

        return ['message' => 'success'];
    }

    public function removeFile($project_id, $id, $type)
    {
        $file = ItemUrl::where(['item_id' => $id, 'type' => $type])->first();

        if (!empty($file)) {
            if ($type == 'las' || $type == 'laz') {
                ItemUrl::manager()->deletePotreeFiles($file->id);
            }

            if (file_exists(public_path(urldecode(preg_replace('/download\?file\=/i', '', $file->url))))) {
                unlink(public_path(urldecode(preg_replace('/download\?file\=/i', '', $file->url))));
            }

            $file->delete();
        }

        return ['message' => 'success'];
    }

}
