<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyAndTypeToVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_visibilities', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->addColumn('integer','company_id')->after('user_id')->unsigned()->nullable();

            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_visibilities', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->change();
            $table->dropColumn('company_id');
        });
    }
}
