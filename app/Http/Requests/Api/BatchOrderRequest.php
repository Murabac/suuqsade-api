<?php

namespace App\Http\Requests\Api;

use App\Rules\ProductLink;
use Illuminate\Foundation\Http\FormRequest;

class BatchOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'links' => ['required', 'array', 'min:1', 'max:10'],
            'links.*' => ['required', 'string', 'max:2000', new ProductLink],
            'notes' => ['sometimes', 'array'],
            'notes.*' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
