<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'default_service_fee_pct' => '10',
            'default_shipping_fee' => '15.00',
            'merchant_number' => '0000000',
            'quote_response_hours' => '24',
            'payment_confirm_minutes' => '30',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }
    }
}
