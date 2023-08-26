<?php

namespace App\Observers;

use App\Jobs\CalculateSize;
use App\Models\ItemUrl;

class ItemUrlObserver
{
    /**
     * Handle the project item url "created" event.
     *
     * @param  \App\Models\ItemUrl  $itemUrl
     * @return void
     */
    public function created(ItemUrl $itemUrl)
    {
        CalculateSize::dispatch($itemUrl);
    }

    /**
     * Handle the project item url "deleting" event.
     *
     * @param  \App\Models\ItemUrl $itemUrl
     * @return void
     */
    public function deleting(ItemUrl $itemUrl)
    {
        CalculateSize::dispatch($itemUrl);
    }
}
