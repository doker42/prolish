<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProjectVisibilitiesChangeForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_visibilities', function (Blueprint $table) {
            $table->dropForeign('project_visibilities_project_id_foreign');
            $table->dropForeign('project_visibilities_user_id_foreign');

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
        Schema::table('project_visibilities', function (Blueprint $table) {
            $table->dropForeign('project_visibilities_project_id_foreign');
            $table->dropForeign('project_visibilities_user_id_foreign');

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
