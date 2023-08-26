<?php

namespace App\Console\Commands;

use App\Jobs\ProcessItems;
use App\Models\ItemUrl;
use App\Models\Potree;
use App\Models\ProjectItem;
use Illuminate\Console\Command;

class ConvertMissingPotree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'potree:missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check which las/laz files are missing potree and create job';

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
        $items = ProjectItem::where('type', 'point_clouds')->get();

        foreach ($items as $item) {
            $url = ItemUrl::where('type', 'las')->where('item_id', $item->id)->first();

            if (!empty($url)) {
                if (empty(Potree::where('item_id', $item->id)->first())) {
                    ProcessItems::dispatch($url);
                }
            }
        }
    }
}
