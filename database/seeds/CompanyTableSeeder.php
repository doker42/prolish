<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('companies')->insert([
            'title' => 'test',
            'logo' => 'https://www.3dskenesana.lv/images/1538757718_5bb794568b4e6.png',
            'status' => 1,
            'storage_used' => 0,
            'owner_id' => 1,
            'membership_id' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }
}
