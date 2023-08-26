<?php

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=0; $i< 8; $i++) {
            Project::create([
                'title' => uniqid(),
                'description' => uniqid(),
                'image' => '/images/450x450.png',
                'status' => rand(0, 1),
                'company_id' => 1
            ]);
        }
    }
}
