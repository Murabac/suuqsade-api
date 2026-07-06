<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            return Setting::query()->find($key)?->value ?? $default;
        });
    }

    public function set(string $key, string $value): Setting
    {
        Cache::forget("setting.{$key}");

        return Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public function publicSettings(): array
    {
        return [
            'merchant_number' => $this->get('merchant_number', '0000000'),
            'default_service_fee_pct' => $this->get('default_service_fee_pct', '10'),
            'default_shipping_fee' => $this->get('default_shipping_fee', '15.00'),
            'quote_response_hours' => $this->get('quote_response_hours', '24'),
            'payment_confirm_minutes' => $this->get('payment_confirm_minutes', '30'),
        ];
    }
}
