<?php

namespace App\Jobs;

use App\Models\ItemUrl;
use App\Models\ProjectItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CloudCompare implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;
    protected $format;
    protected $potree;
    protected $download_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ItemUrl $item, $format, $potree, $download_id = false)
    {
        $this->item = $item;
        $this->format = $format;
        $this->potree = $potree;
        $this->download_id = $download_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->item)) {
            $file = public_path($this->item->url);

            if ($this->download_id) {
                $filename = uniqid();
                $file =  storage_path('/app/tmp/' . $filename . '.' . $this->item->type);
                exec('gdown https://drive.google.com/uc?id=' . $this->download_id . ' -O ' . $file);

                if (!file_exists($file)) {
                    var_dump('CloudCompare failed to download file from google with id: ' . $this->download_id);
                }
            }

            if (file_exists($file)) {
                $projectItem = ProjectItem::find($this->item->item_id);
                $projectItem->status = 'custom.item_status_cc_converting||' . $this->format;
                $projectItem->save();

                try {
                    $filename = pathinfo($this->item->url, PATHINFO_FILENAME) . '-' . uniqid() . '.' . $this->format;
                    $folder = 'uploads/documents/' . $projectItem->project_id;

                    if (in_array($this->format, ['pts', 'ptx', 'xyz'])) {
                        $format = 'ASC -EXT ' . $this->format;
                    } else {
                        $format = strtoupper($this->format);
                    }

                    $time_start = microtime(true);
                    exec('cd ' . public_path($folder) . ' && cpulimit -l 40 -- timeout 6100 xvfb-run -a /usr/local/E57Format-2.0-x86_64-linux-gcc7/bin/CloudCompare -SILENT -C_EXPORT_FMT ' . $format . ' -O ' . $file . ' -MERGE_CLOUDS -SAVE_CLOUDS FILE ' . $filename . ' 2>&1', $output);
                    $time_end = microtime(true);

                    // Rename merged clouds file
                    if (file_exists(public_path($folder . '/' . $filename . '_0'))) {
                        rename(public_path($folder . '/' . $filename . '_0'), public_path($folder . '/' . $filename));
                    }

                    if (($time_end - $time_start)/60 > 6090) {
                        throw new \Exception('custom.item_status_failed_timeout');
                    } else if (!file_exists(public_path($folder . '/' . $filename))) {
                        var_dump($output);
                        var_dump('CC Failed to convert');
                        throw new \Exception('custom.item_status_failed');
                    }

                    $item_url = ItemUrl::create([
                        'item_id' => $this->item->item_id,
                        'url' => $folder . '/' . $filename,
                        'type' => $this->format
                    ]);

                    if ($this->potree) {
                        // Delete old file
                        $old_item = ItemUrl::where(['item_id' => $this->item->item_id, 'type' => 'las'])->where('id', '!=', $item_url->id)->first();
                        if (!empty($old_item)) {
                            if (file_exists(public_path($old_item->url))) {
                                unlink(public_path($old_item->url));
                            }

                            ItemUrl::manager()->deletePotreeFiles($old_item->id);
                            $old_item->delete();
                        }

                        ProcessItems::dispatch($item_url);
                    } else {
                        $projectItem->status = 'custom.item_status_success';
                        $projectItem->save();
                    }

                    if ($this->download_id) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'custom.item_status_success') {
                        $projectItem->status = $e->getMessage();
                    } else {
                        var_dump('CC Failed to convert with error: ' . $e->getMessage());
                        $projectItem->status = 'custom.item_status_success';
                    }
                    $projectItem->save();
                    $this->fail();
                }
            }
        }
    }

}
