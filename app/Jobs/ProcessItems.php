<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Potree;
use App\Models\ItemUrl;
use App\Models\Project;
use App\Models\ProjectItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;
    protected $download_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ItemUrl $item, $download_id = false)
    {
        $this->item = $item;
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
                    var_dump('PotreeConvert failed to download file from google with id: ' . $this->download_id);
                }
            }

            if (file_exists($file)) {
                $projectItem = ProjectItem::find($this->item->item_id);

                try {
                    if (!$this->canUpload($file, $projectItem->project_id)) {
                        throw new \Exception('custom.item_status_failed_size');
                    }

                    $output_dir = public_path('potree/');

                    $projectItem->status = 'custom.item_status_converting';
                    $projectItem->save();

                    $potree_id = uniqid();

                    $time_start = microtime(true);
                    exec('cpulimit -l 40 -- timeout 6100 ' . storage_path('/app/potree/') . 'PotreeConverter ' . $file . ' --output-format LAZ -o ' . $output_dir . ' -p ' . $potree_id);
                    $time_end = microtime(true);

                    if (($time_end - $time_start)/60 > 6090) {
                        throw new \Exception('custom.item_status_failed_timeout');
                    }

                    $potree = Potree::create([
                        'item_id' => $this->item->item_id,
                        'filename' => $potree_id
                    ]);

                    // Calculate size
                    $file_size = 0;

//                    foreach( File::allFiles($output_dir . $potree_id) as $file) {
//                        $file_size += $file->getSize();
//                    }
//
//                    $file_size = number_format($file_size / 1048576,2);

                    $projectItem->size = $file_size;
                    $projectItem->view_url = '/potree/' . $potree->id;

//                    $this->updateCompanySize($file_size, $projectItem->project_id);

                    $projectItem->status = 'custom.item_status_success';
                    $projectItem->save();


                    if ($this->download_id) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                } catch (\Exception $e) {
                    if ($e->getMessage() == 'custom.item_status_failed_size') {
                        $projectItem->status = $e->getMessage();
                    } else {
                        var_dump('PotreeConvert failed with error: ' . $e->getMessage());
                        $projectItem->status = 'custom.item_status_failed';
                    }
                    $projectItem->save();
                    $this->fail();
                }
            }
        }
    }

    private function canUpload($file, $project_id)
    {
        return true;
        $project = Project::find($project_id);
        $company = Company::find($project->company_id);

        $size = $company->storage_used + number_format(filesize($file) / 1048576,2, ".", "");

        $membership = Membership::find($company->membership_id);

        return ($size > $membership->size) ? false : true;
    }

    private function updateCompanySize($size, $project_id)
    {
        $project = Project::find($project_id);
        $company = Company::find($project->company_id);

        $company->storage_used += $size;
        $company->save();
    }
}
