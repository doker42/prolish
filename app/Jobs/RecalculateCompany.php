<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\ItemUrl;
use App\Models\Project;
use App\Models\ProjectItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RecalculateCompany implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $projects = Project::where('company_id', $this->company->id)->get();
        $size = 0;

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
                            $size += $mbs;

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

        $this->company->storage_used = $size;
        $this->company->save();
    }

}
