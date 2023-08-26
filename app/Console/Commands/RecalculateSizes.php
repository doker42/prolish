<?php

namespace App\Console\Commands;

use App\Models\ItemUrl;
use App\Models\Project;
use App\Models\ProjectItem;
use Illuminate\Console\Command;

class RecalculateSizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sizes:recalculates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $projects = Project::all();

        foreach ($projects as $project) {
            $project_size = 0;
            $items = ProjectItem::where('project_id', $project->id)->get();

            foreach ($items as $item) {
                $item_size = 0;
                $item_urls = ItemUrl::where('item_id', $item->id)->get();

                foreach ($item_urls as $item_url) {
                    $path = public_path(urldecode(preg_replace('/download\?file\=/i', '', $item_url->url)));
                    if (file_exists($path)) {
                        $bytes = filesize($path);

                        if ($bytes > 0) {
                            $mbs = number_format($bytes / 1048576, 2, ".", "");

                            $item_size += $mbs;
                            $project_size += $mbs;

                            if ($item_url->size != $mbs) {
                                ItemUrl::where('id', $item_url->id)->update(['size' => $mbs]);
                            }
                        }
                    }
                }

                if ($item->size != $item_size) {
                    ProjectItem::where('id', $item->id)->update(['size' => $item_size]);
                }
            }

            if ($project->size != $project_size) {
                Project::where('id', $project->id)->update(['size' => $project_size]);
            }
        }
    }
}
