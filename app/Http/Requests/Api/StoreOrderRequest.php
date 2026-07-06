<?php

namespace App\Http\Requests\Api;

use App\Rules\ProductLink;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_link' => ['required', 'string', 'max:2000', new ProductLink],
            'product_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
