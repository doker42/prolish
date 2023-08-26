<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserFavouriteProjectsChangeForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_favourite_projects', function (Blueprint $table) {
            $table->dropForeign('user_favourite_projects_project_id_foreign');
            $table->dropForeign('user_favourite_projects_user_id_foreign');

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_favourite_projects', function (Blueprint $table) {
            $table->dropForeign('user_favourite_projects_project_id_foreign');
            $table->dropForeign('user_favourite_projects_user_id_foreign');

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
