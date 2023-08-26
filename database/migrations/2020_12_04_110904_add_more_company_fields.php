<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreCompanyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('industry_id')->after('company_bank')->nullable();
            $table->integer('employees_number')->after('company_bank')->nullable();
            $table->string('vat_number')->after('company_bank')->nullable();
            $table->string('office_address')->after('company_bank')->nullable();
            $table->string('phone')->after('company_bank')->nullable();
            $table->string('email')->after('company_bank')->nullable();
            $table->string('facebook')->after('company_bank')->nullable();
            $table->string('linkedin')->after('company_bank')->nullable();
            $table->string('founder')->after('company_bank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('industry_id');
            $table->dropColumn('employees_number');
            $table->dropColumn('vat_number');
            $table->dropColumn('office_address');
            $table->dropColumn('phone');
            $table->dropColumn('email');
            $table->dropColumn('facebook');
            $table->dropColumn('linkedin');
            $table->dropColumn('founder');
        });
    }
}
