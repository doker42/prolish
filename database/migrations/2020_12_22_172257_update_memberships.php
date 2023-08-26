<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMemberships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('type');
            $table->string('month_stripe_sub_key')->after('title');
            $table->string('year_stripe_sub_key')->after('title');
            $table->string('month_stripe_key')->after('title');
            $table->string('year_stripe_key')->after('title');
            $table->double('month_price', 2)->after('title');
            $table->double('year_price', 2)->after('title');

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
            $table->dropColumn('month_price');
            $table->dropColumn('year_price');
            $table->dropColumn('month_stripe_sub_key');
            $table->dropColumn('year_stripe_sub_key');
            $table->dropColumn('month_stripe_key');
            $table->dropColumn('year_stripe_key');
            $table->string('type')->after('title');
            $table->double('price', 2)->after('title');

        });//
    }
}
