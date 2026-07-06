<?php

namespace App\Rules;

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

        $host = strtolower(parse_url($value, PHP_URL_HOST) ?? '');

        if ($host === '') {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        if (str_contains($host, 'shein.com')) {
            return;
        }

        if (preg_match('/(^|\.)amazon\./', $host)) {
            return;
        }

        $fail('Only Shein and Amazon links are supported for now.');
    }
}
