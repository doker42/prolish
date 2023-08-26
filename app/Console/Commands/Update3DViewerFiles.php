<?php

namespace App\Console\Commands;

use App\Models\ItemUrl;
use App\Models\ProjectItem;
use Illuminate\Console\Command;

class Update3DViewerFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '3dviewer_urls:fix';

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
        $items = ItemUrl::whereIn('type', ['stl', '3ds', 'obj'])->get();
        $found = 0;

        foreach ($items as $item) {
            $found++;

            ProjectItem::where('id', $item->item_id)->update(['view_url' => '/3d-viewer/#/' . $item->url]);
        }

        die('Found: '. $found . "\n");
    }
}
