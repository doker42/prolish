<?php
declare(strict_types=1);

namespace App\Domain\Project;
use Illuminate\Support\Facades\File;
use App\Foundation\Bridge\Laravel\UpTrait;
use App\Models\ItemUrl;
use App\Models\Potree;
use App\Models\ProjectItem;
use App\Models\ProjectLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ItemManager
{
    use UpTrait;

    public function delete(int $id):void
    {
        $item = ProjectItem::withTrashed()->where('id', $id)->first();

        $urls = ItemUrl::where('item_id', $id)->get();

        foreach ($urls as $url) {
            if ($url->type == 'laz' || $url->type == 'las') {
                ItemUrl::manager()->deletePotreeFiles($url->id);
            } else if ($url->type == 'pdf') {
                $path = public_path(urldecode(preg_replace('/download\?file\=/i', '', $url->url)));
                if (file_exists($path)) {
                    unlink($path);
                }
            } else if ($url->type == 'zip') {
                $path = 'zip/' . $item->type . '/' . $id . '/' . $url->id . '/';
                if (file_exists(public_path($path))) {
                    Storage::deleteDirectory(public_path($path));
                }
            }
        }

        ProjectLog::create([
            'user_id' => Auth::user()->id,
            'project_id' => $item->project_id,
            'trans' => 'logs_item_force_deleted',
            'data' => [
                'title' => $item->title
            ]
        ]);

        $item->forceDelete();
    }

    public function copyItem(int $id, int $project_id):void
    {
        $item = ProjectItem::where('id', $id)->first();
        if(!empty($item)){
            $new_item = $item->replicate();
            $new_item->project_id = $project_id;
            $new_item->save();
            $item_urls =  ItemUrl::where('item_id', $id)->get();
            $view_link = $item->view_url;
            foreach($item_urls as $item_url){
                $new_item_url = $item_url->replicate();
                $new_item_url->item_id = $new_item->id;
                if (substr($item_url->url, 0, 4) !== 'http') {
                    $path_info = pathinfo($item_url->url);
                    if ($path_info['dirname'] == 'uploads/documents/' . $item->project_id) {
                        $new_file_name = 'uploads/documents/' . $project_id . '/' . $path_info['basename'];
                    } else {
                        $new_file_name = 'uploads/documents/' . Carbon::now()->timestamp . '_' . uniqid() . '.' . $path_info['extension'];
                    }
                    $new_item->save();
                    if (Storage::disk('local')->exists($item_url->url)) {
                        Storage::disk('local')->copy($item_url->url, $new_file_name);
                    }
                    if ($view_link == $item_url->url) {
                        $new_item->view_url = $new_item_url->url;
                        $new_item->save();
                    }
                    if ($view_link == '/3d-viewer/#/' . $item_url->url) {
                        $new_item->view_url = '/3d-viewer/#/' . $new_item_url->url;
                        $new_item->save();
                    }
                }else {
                    $new_item->view_url = $new_item_url->url;
                    $new_item->save();
                }
                $new_item_url->save();
            }
            $potree = Potree::where('item_id', $item->id)->first();
            if(!empty($potree)){
                $potree_id = uniqid();
                $new_potree = Potree::create([
                    'item_id' => $new_item->id,
                    'filename' => $potree_id
                ]);
                $new_item->view_url = 'potree/' . $new_potree->id;
                File::copyDirectory(public_path($potree->pointcloud), public_path($new_potree->pointcloud));
            }
        }
    }
}