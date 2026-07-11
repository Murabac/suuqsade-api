<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $legacyMerchant = Setting::query()->find('merchant_number')?->value;

        Setting::query()->updateOrCreate(
            ['key' => 'zaad_merchant_number'],
            ['value' => Setting::query()->find('zaad_merchant_number')?->value ?? $legacyMerchant ?? '487960'],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'edahab_merchant_number'],
            ['value' => Setting::query()->find('edahab_merchant_number')?->value ?? '19272'],
        );
    }

    public function down(): void
    {
        Setting::query()->whereIn('key', [
            'zaad_merchant_number',
            'edahab_merchant_number',
        ])->delete();
    }
};
