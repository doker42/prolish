<?php

use Illuminate\Database\Seeder;
use App\Models\Membership;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $memberships = [
            1 => [
                'title' => 'Free',
                'month_price' => 0.00,
                'year_price' => 0.00,
                'year_stripe_key' => 'price_',
                'month_stripe_key' => 'price_',
                'year_stripe_sub_key' =>  'price_',
                'month_stripe_sub_key' => 'price_',
                'size' => 15 * 1000,
                'managers_limit' => 1,
                'projects_limit' => 3,
                'visitors_limit' => 2,
                'overlimit_gb_price' => 0,
                'conversions_limit' => 10,
                'support_type' => 1,
            ],
            2 => [
                'title' => 'Basic',
                'month_price' => 49.99,
                'year_price' => 449.99,
                'year_stripe_key' => 'price_1I6C0RI6veN8F8SmOE67jxFA',
                'month_stripe_key' => 'price_1I6C0RI6veN8F8SmPOhBEyyz',
                'year_stripe_sub_key' =>  'price_1I6C0RI6veN8F8SmTsraYrYu',
                'month_stripe_sub_key' => 'price_1I6C0RI6veN8F8SmaJ8B3oIw',
                'size' => 100 * 1000,
                'managers_limit' => 3,
                'projects_limit' => 30,
                'visitors_limit' => 10,
                'overlimit_gb_price' => 99,
                'conversions_limit' => 0,
                'support_type' => 2,
            ],
            3 => [
                'title' => 'Pro',
                'month_price' => 69.99,
                'year_price' => 699.99,
                'year_stripe_key' => 'price_1I6C0EI6veN8F8SmyPXXd4aW',
                'month_stripe_key' => 'price_1I6C0EI6veN8F8SmrYWgHzvb',
                'year_stripe_sub_key' => 'price_1I6C0EI6veN8F8SmaMEXLNTn',
                'month_stripe_sub_key' => 'price_1I6C0EI6veN8F8SmJT7FvltA',
                'size' => 300 * 1000,
                'managers_limit' => 10,
                'projects_limit' => 100,
                'visitors_limit' => 50,
                'overlimit_gb_price' => 69,
                'conversions_limit' => 0,
                'support_type' => 2,
            ],
            4 => [
                'title' => 'Ultimate',
                'month_price' => 99.99,
                'year_price' => 999.99,
                'year_stripe_key' => 'price_1I6C00I6veN8F8Sm4OlIqShS',
                'month_stripe_key' => 'price_1I6C00I6veN8F8SmWKe7rGKe',
                'month_stripe_sub_key' => 'price_1I6C00I6veN8F8SmJn89zShp',
                'year_stripe_sub_key' => 'price_1I6C00I6veN8F8Smty4RlG86',
                'size' => 1000 * 1000,
                'managers_limit' => 0,
                'projects_limit' => 0,
                'visitors_limit' => 0,
                'overlimit_gb_price' => 49,
                'conversions_limit' => 0,
                'support_type' => 3,
            ],
        ];

        foreach($memberships as $key => $data){
            $membership = Membership::where('id', $key)->first();
            if (!empty($membership)){
                $membership->title = $data['title'];
                $membership->month_price = $data['month_price'];
                $membership->year_price = $data['year_price'];
                $membership->year_stripe_key = $data['year_stripe_key']??'';
                $membership->month_stripe_key = $data['month_stripe_key']??'';
                $membership->year_stripe_sub_key = $data['year_stripe_sub_key']??'';
                $membership->month_stripe_sub_key = $data['month_stripe_sub_key']??'';
                $membership->size = $data['size'];
                $membership->managers_limit = $data['managers_limit'];
                $membership->projects_limit = $data['projects_limit'];
                $membership->visitors_limit = $data['visitors_limit'];
                $membership->overlimit_gb_price = $data['overlimit_gb_price'];
                $membership->conversions_limit = $data['conversions_limit'];
                $membership->support_type = $data['support_type'];
                $membership->save();
            } else {
                Membership::create($data);
            }
        }

    }
}
