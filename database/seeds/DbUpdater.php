<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DbUpdater extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $no_avatars_users = \App\Models\User::where('picture', '/images/450x450.png')->get();
       foreach ($no_avatars_users as $user){
           $user->picture = null;
           $user->save();
       }
        $wrong_types_items = \App\Models\ProjectItem::where('type', '2d_drawins')->orWhere('type', '2d_drowins')->get();
        foreach ($wrong_types_items as $item){
            $item->type = '2d_drawings';
            $item->save();
        }
    }
}
