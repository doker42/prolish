<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => str_random(10),
            'email' => 'tayfun@dvb.lv',
            'password' => bcrypt('secret'),
            'role' => 'super_user',
            'company_id' => 1
        ]);
    }
}
