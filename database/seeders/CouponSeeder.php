<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'WELCOME10', 'name' => 'Welcome 10%', 'type' => 'percentage', 'value' => 10, 'usage_limit_per_customer' => 1],
            ['code' => 'SAVE20', 'name' => '20 off orders over 100', 'type' => 'fixed_amount', 'value' => 20, 'min_order_amount' => 100],
            ['code' => 'FREESHIP', 'name' => 'Free shipping', 'type' => 'free_shipping', 'value' => 0],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon + ['is_active' => true]);
        }
    }
}
