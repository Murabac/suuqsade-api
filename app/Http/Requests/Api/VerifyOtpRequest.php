<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'code' => ['required', 'string', 'size:6'],
            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
