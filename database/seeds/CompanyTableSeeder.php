<?php

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
            'storage_used' => 0
        ]);
    }
}
