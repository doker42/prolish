<?php

namespace App\Http\Controllers;

use App\Models\Potree;
use Illuminate\Http\Request;
use App\Models\ProjectItem;

class PotreeController extends Controller
{
    public function show(Request $request, $id)
    {
        $potree = Potree::find($id);
        $item = ProjectItem::find($potree->item_id);
        $items = ProjectItem::where('project_id', $item->project_id)->where('id', '!=', $potree->item_id)->with('potree')->get();

        return view('potree', [
            'main' => $item,
            'sub' => $items
        ]);
    }

    public function destroy($id)
    {
        Potree::find($id)->delete();
        return ['message' => 'success'];
    }

    public function types()
    {
        return config('allowed_types');
    }
}
