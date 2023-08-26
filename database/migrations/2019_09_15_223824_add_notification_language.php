<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationLanguage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('title', 'title_en');
            $table->renameColumn('content', 'content_en');
            $table->text('content_lv')->nullable();
            $table->text('content_ru')->nullable();
            $table->text('content_et')->nullable();

            $table->string('title_lv')->nullable();
            $table->string('title_ru')->nullable();
            $table->string('title_et')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('title_en', 'title');
            $table->renameColumn('content_en', 'content');
            $table->dropColumn('content_lv');
            $table->dropColumn('content_ru');
            $table->dropColumn('content_et');
            $table->dropColumn('title_lv');
            $table->dropColumn('title_ru');
            $table->dropColumn('title_et');
        });
    }
}
