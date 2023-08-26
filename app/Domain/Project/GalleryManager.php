<?php
declare(strict_types=1);

namespace App\Domain\Project;

use App\Foundation\Bridge\Laravel\UpTrait;
use App\Models\ProjectGalleryFolder;
use App\Models\ProjectGalleryImage;
use App\Models\ProjectLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryManager
{
    use UpTrait;

    public function deleteFolder(int $project_id, int $folder_id):void
    {
        $images = ProjectGalleryImage::where(['folder_id' => $folder_id, 'project_id' => $project_id])->get();

        foreach ($images as $image) {
            if (file_exists(public_path($image->url))) {
                unlink(public_path($image->url));
            }
            $image->delete();
        }

        ProjectGalleryFolder::where(['id' => $folder_id, 'project_id' => $project_id])->delete();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $project_id,
            'trans' => 'logs_gallery_folder_delete',
            'data' => [
                'folder_id' => $folder_id
            ]
        ]);
    }


    public function copyImage(int $image_id, int $project_id)
    {
        $image = ProjectGalleryImage::where('id', $image_id)->first();
        if (!empty($image)) {
            $path_parts = pathinfo($image->url);
            $fileName = Carbon::now()->timestamp . '_' . uniqid() . '.' . $path_parts['extension'];
            if (Storage::disk('local')->exists($image->url)) {
                Storage::disk('local')->copy($image->url, '/images/' . $fileName);
            }
            $new_image = ProjectGalleryImage::create([
                'project_id' => $project_id,
                'url' => '/images/' . $fileName,
            ]);
            if ($image->folder_id > 0) {
                $folder = ProjectGalleryFolder::where('id', $image->folder_id)->first();
                if ($folder) {
                    $new_folder = ProjectGalleryFolder::where('project_id', $project_id)->where('title', $folder->title)->first();
                    if (empty($new_folder)) {
                        $new_folder = ProjectGalleryFolder::create([
                            'project_id' => $project_id,
                            'title' => $folder->title,
                        ]);
                    }
                    $new_image->folder_id = $new_folder->id;
                    $new_image->save();
                }

            }
        }
    }


    public function deleteImage(int $image_id):void{

        $image = ProjectGalleryImage::where('id', $image_id)->first();
        if (file_exists(public_path($image->url))) {
            unlink(public_path($image->url));
        }

        $image->delete();

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $image->project_id,
            'trans' => 'logs_gallery_delete',
            'data' => [
                'url' => $image_id
            ]
        ]);
    }
}