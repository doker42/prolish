<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PotreeChangeForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('potree', function (Blueprint $table) {
            $table->dropForeign('potree_item_id_foreign');
            $table->foreign('item_id')->references('id')->on('project_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('potree', function (Blueprint $table) {
            $table->dropForeign('potree_item_id_foreign');
            $table->foreign('item_id')->references('id')->on('project_items');
        });
    }
}
