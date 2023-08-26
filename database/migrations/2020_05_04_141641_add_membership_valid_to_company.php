<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMembershipValidToCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->addColumn('timestamp', 'active_until')->nullable()->after('membership_id');
        });

        $date = \Carbon\Carbon::now();

        \App\Models\Company::where('membership_id', '>', 1)->where('parent_company', '!=', null)->update([
            'active_until' => $date->addMonths(2)
        ]);

        \App\Models\Company::where('parent_company', null)->update([
            'active_until' => $date->addYears(10)
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('active_until');
        });
    }
}
