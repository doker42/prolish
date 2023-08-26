<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGalleryFolders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_gallery_folders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::table('project_gallery_images', function (Blueprint $table) {
            $table->addColumn('integer','folder_id')->after('url')->unsigned()->nullable();

            $table->foreign('folder_id')->references('id')->on('project_gallery_folders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_gallery_folders');

        Schema::table('project_gallery_images', function (Blueprint $table) {
            $table->dropColumn('folder_id');
        });
    }
}
