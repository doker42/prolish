<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectGalleryFolder;
use App\Models\ProjectGalleryImage;
use App\Models\ProjectLog;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectGalleryController extends Controller
{
    public function index(Request $request, $id)
    {
        $images = ProjectGalleryImage::where(['project_id' => $id, 'folder_id' => $request->get('folder_id')])->get();

        if(!$request->get('folder_id')) {
            $folders = ProjectGalleryFolder::where('project_id', $id)->get();
        }

        return [
            'folders' => $folders ?? null,
            'urls' => $images->pluck('url'),
            'ids' => $images->pluck('id')
        ];
    }

    /**
     * Download gallery folder in zip archive
     *
     * @param  int  $folder_id
     *
     * @return Response
     */
    public function downloadGalleryFolder($folder_id){

        $images = ProjectGalleryImage::where(['folder_id' => $folder_id])->get();

        if($images->count() == 0){
            throw new \Exception(trans('custom.no_folder_content_fount'));
        }

        $folder = ProjectGalleryFolder::where(['id'=>$folder_id])->first();

        $zipfileName = str_replace( ' ', '_', trim($folder->title).'_gallery.zip');

        $zip_file = public_path('/uploads/'.$zipfileName);

        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);


        foreach ($images as $image){
            $zip->addFromString(basename($image->url), file_get_contents(public_path($image->url)));
        }

        $zip->close();
        $headers = array(
            'Content-Type' => 'application/octet-stream',
        );

        return response()->download($zip_file,$zipfileName, $headers);
    }

    /**
     * Download gallery folder in zip archive
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function downloadGallery($id){

        $images = ProjectGalleryImage::where(['project_id' => $id])->get();

        if($images->count() == 0){
            throw new \Exception(trans('custom.no_folder_content_fount'));
        }

        $project = Project::where(['id'=>$id])->first();

        $zipfileName = str_replace( ' ', '_', trim($project->title).'_gallery.zip');

        $zip_file = public_path('/uploads/'.$zipfileName);

        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);


        foreach ($images as $image){
            $image_path = !empty($image->folders)?$image->folders->title.'/'.basename($image->url):basename($image->url);
            $zip->addFromString($image_path, file_get_contents(public_path($image->url)));
        }

        $zip->close();
        $headers = array(
            'Content-Type' => 'application/octet-stream',
        );
        return response()->download($zip_file,$zipfileName, $headers);
    }

    public function store(Request $request, $id)
    {
        if ($request->get('url')) {
            $image = ProjectGalleryImage::create([
                'project_id' => $id,
                'folder_id' => $request->get('folder_id'),
                'url' => $request->get('url')
            ]);

            ProjectLog::create([
                'user_id' => Auth::user()->id,
                'project_id' => $id,
                'trans' => 'logs_gallery_add',
                'data' => [
                    'id' => $image->id,
                    'url' => $request->get('url')
                ]
            ]);

            return ['id' => $image->id];
        }

        return ['message' => 'success'];
    }


    public function destroy($id, $image_id)
    {
        $image = ProjectGalleryImage::find($image_id);

        unlink(public_path($image->url));

        $image->delete();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_gallery_delete',
            'data' => [
                'url' => $image_id
            ]
        ]);

        return ['message' => 'success'];
    }

    public function create_folder(Request $request, $id)
    {
        $folder = ProjectGalleryFolder::create([
            'project_id' => $id,
            'title' => $request->get('title')
        ]);

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $id,
            'trans' => 'logs_gallery_folder_add',
            'data' => [
                'title' => $request->get('title')
            ]
        ]);

        return ['id' => $folder->id];
    }

    public function destroy_folder($id, $folder_id)
    {
        ProjectGalleryFolder::manager()->deleteFolder($id, $folder_id);

        return ['message' => 'success'];
    }
}
