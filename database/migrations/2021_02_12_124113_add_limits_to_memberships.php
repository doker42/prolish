<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLimitsToMemberships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->integer('managers_limit')->after('size');
            $table->integer('projects_limit')->after('size');
            $table->integer('visitors_limit')->after('size');
            $table->integer('overlimit_gb_price')->after('size');
            $table->integer('conversions_limit')->after('size');
            $table->tinyInteger('support_type')->after('size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('managers_limit');
            $table->dropColumn('projects_limit');
            $table->dropColumn('visitors_limit');
            $table->dropColumn('overlimit_gb_price');
            $table->dropColumn('conversions_limit');
            $table->dropColumn('support_type');
        });
    }
}
