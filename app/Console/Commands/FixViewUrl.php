<?php

namespace App\Console\Commands;

use App\Models\ItemUrl;
use App\Models\ProjectItem;
use Illuminate\Console\Command;

class FixViewUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view_url:fix';

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
        $projects = ProjectItem::where('type', '!=', '360_photo_tours')->get();
        $found = 0;

        foreach ($projects as $project) {
            ItemUrl::where(['item_id' => $project->id, 'type' => 'view_url'])->delete();
            $found++;

//            if (!empty($project->view_url)) {
//                $item = ItemUrl::where(['item_id' => $project->id, 'type' => 'view_url'])->first();
//                if (empty($item)) {
//                    ItemUrl::create([
//                        'item_id' => $project->id,
//                        'url' => $project->view_url,
//                        'type' => 'view_url'
//                    ]);
//
//                    $found++;
//                }
//            }
        }

        die('Found: '. $found . "\n");
    }
}
