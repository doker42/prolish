<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_name', 255)->nullable()->after('logo');
            $table->string('company_number', 255)->nullable()->after('company_name');
            $table->string('company_address', 1000)->nullable()->after('company_number');
            $table->string('company_account_number', 255)->nullable()->after('company_address');
            $table->string('company_bank',255)->nullable()->after('company_account_number');
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
            $table->dropColumn('company_name');
            $table->dropColumn('company_number');
            $table->dropColumn('company_address');
            $table->dropColumn('company_account_number');
            $table->dropColumn('company_bank');
        });
    }
}
