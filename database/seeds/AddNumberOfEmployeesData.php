<?php

use Illuminate\Database\Seeder;

class AddNumberOfEmployeesData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employee_numabers = [
            'SMB <250',
            'Mid-Market 250-1500',
            'Enterprise >1500'
        ];

        foreach($employee_numabers as $value){
            DB::table('employee_numbers')->insert([
                'title' => $value,
            ]);
        }
    }
}
