<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddSpecializationsData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()


    {
        DB::table('companies_specialization')->delete();
        DB::table('specializations')->delete();
        Schema::dropIfExists('companies_specialization');
        DB::table('specializations')->truncate();
        Schema::create('companies_specialization', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('specialization_id')->unsigned();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('specialization_id')->references('id')->on('specializations');

            $table->timestamps();
        });
        $specializations = fopen(__DIR__.'/../specializations.csv', 'r');

        while (($row = fgetcsv($specializations, 0, ',')) !=FALSE){
           $value = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', trim(implode(',',$row)));
            DB::table('specializations')->insert(
                array(
                    'title' =>  $value,
                )
            );
        }
    }

}
