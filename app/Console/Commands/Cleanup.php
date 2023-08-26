<?php

namespace App\Console\Commands;

use App\Models\ItemUrl;
use App\Models\ProjectItem;
use App\Models\Potree;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Cleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old files and soft deletes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Remove old .laz files
        $files = Storage::disk('storage')->allFiles('app/laz/');
        $deleted_files = 0;

        foreach ($files as $file) {
            if (Storage::disk('storage')->lastModified($file) <= strtotime("-1 week")) {
                Storage::disk('storage')->delete($file);
                $deleted_files++;
            }
        }

        // Force delete soft deletes
        $deleted_items = 0;
        $items = ProjectItem::onlyTrashed()->where('deleted_at', '<', Carbon::now()->subDays(30))->get();

        foreach ($items as $item) {
            $urls = ItemUrl::where('item_id', $item->id)->get();

            foreach ($urls as $url) {
                if ($url->type == 'laz') {
                    $potree = Potree::where('item_id', $url->id)->first();

                    if ($potree && file_exists(public_path('potree/' . $potree->filename . '.html'))) {
                        unlink(public_path('potree/' . $potree->filename . '.html'));
                        Storage::deleteDirectory(public_path('potree/pointcoulds/' . $potree->filename));
                    }
                } else if ($url->type == 'zip') {
                    $path = 'zip/' . $item->type . '/' . $item->id . '/' . $url->id . '/';
                    if (file_exists(public_path($path))) {
                        Storage::deleteDirectory(public_path($path));
                    }
                } else if (file_exists(public_path(urldecode(preg_replace('/download\?file\=/i', '', $url->url))))) {
                    unlink(public_path(urldecode(preg_replace('/download\?file\=/i', '', $url->url))));
                }
            }

            $item->forceDelete();
            $deleted_items++;
        }

        die('Deleted files: '. $deleted_files . "\n" . 'Deleted items: ' . $deleted_items . "\n");
    }
}
