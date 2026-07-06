<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'language' => ['sometimes', 'string', Rule::in(['so', 'en', 'ar'])],
            'delivery_address' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
