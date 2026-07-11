<?php

namespace App\Rules;

use App\Support\SupportedProductHost;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductLink implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        if (! SupportedProductHost::isAllowed($value)) {
            $fail('Only Shein and Amazon links are supported for now.');
        }
    }
}
