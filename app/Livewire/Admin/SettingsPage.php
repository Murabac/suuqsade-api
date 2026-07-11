<?php

namespace App\Livewire\Admin;

use App\Services\SettingsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Settings')]
class SettingsPage extends Component
{
    public string $default_service_fee_pct = '';

    public string $default_shipping_fee = '';

    public string $zaad_merchant_number = '';

    public string $edahab_merchant_number = '';

    public string $quote_response_hours = '';

    public string $payment_confirm_minutes = '';

    public bool $saved = false;

    public function mount(SettingsService $settings): void
    {
        $public = $settings->publicSettings();

        $this->default_service_fee_pct = $public['default_service_fee_pct'];
        $this->default_shipping_fee = $public['default_shipping_fee'];
        $this->zaad_merchant_number = $public['zaad_merchant_number'];
        $this->edahab_merchant_number = $public['edahab_merchant_number'];
        $this->quote_response_hours = $public['quote_response_hours'];
        $this->payment_confirm_minutes = $public['payment_confirm_minutes'];
    }

    public function save(SettingsService $settings): void
    {
        $this->validate([
            'default_service_fee_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_shipping_fee' => ['required', 'numeric', 'min:0'],
            'zaad_merchant_number' => ['required', 'string', 'max:50'],
            'edahab_merchant_number' => ['required', 'string', 'max:50'],
            'quote_response_hours' => ['required', 'integer', 'min:1'],
            'payment_confirm_minutes' => ['required', 'integer', 'min:1'],
        ]);

        foreach ([
            'default_service_fee_pct' => $this->default_service_fee_pct,
            'default_shipping_fee' => number_format((float) $this->default_shipping_fee, 2, '.', ''),
            'zaad_merchant_number' => $this->zaad_merchant_number,
            'edahab_merchant_number' => $this->edahab_merchant_number,
            'quote_response_hours' => $this->quote_response_hours,
            'payment_confirm_minutes' => $this->payment_confirm_minutes,
        ] as $key => $value) {
            $settings->set($key, (string) $value);
        }

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.settings-page');
    }
}
