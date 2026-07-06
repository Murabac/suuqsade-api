<?php

return [
    'fixed_code' => env('OTP_FIXED_CODE', '123456'),
    'bypass' => filter_var(env('OTP_BYPASS', true), FILTER_VALIDATE_BOOLEAN),
];
