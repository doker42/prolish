<?php
declare(strict_types=1);

namespace App\Domain\Project;

use App\Foundation\Bridge\Laravel\UpTrait;
use App\Models\Potree;
use Illuminate\Support\Facades\Storage;

class ItemUrlManager
{
    use UpTrait;

    public function deletePotreeFiles(int $item_id):void
    {
        $potree = Potree::where('item_id', $item_id)->first();
        if ($potree && file_exists(public_path('potree/' . $potree->filename . '.html'))) {
            unlink(public_path('potree/' . $potree->filename . '.html'));
            Storage::deleteDirectory(public_path('potree/pointcoulds/' . $potree->filename));
        }
    }
}