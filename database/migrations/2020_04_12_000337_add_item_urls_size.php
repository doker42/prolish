<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItemUrlsSize extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_urls', function (Blueprint $table) {
            $table->addColumn("float", "size")->nullable()->default('0.00')->after("type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_urls', function (Blueprint $table) {
            $table->dropColumn("size");
        });
    }
}
