<?php

use Illuminate\Database\Seeder;

class AddIndustries extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $industry_values = [
            'surveying',
            'architecture_engineering_construction',
            'industrial_manufacturing',
            'metalworking',
            'mining',
            'real_estate',
            'government_law_enforcement',
            'science_education',
            'medicine_and_pharmaceutics',
            'insurance',
            'transport_logistics',
            'photography_service',
            'other',
        ];

        foreach($industry_values as $value){
            DB::table('industries')->insert([
                'code' => $value,
            ]);
        }

    }
}
