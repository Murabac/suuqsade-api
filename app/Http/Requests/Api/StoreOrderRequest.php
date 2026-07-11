<?php

namespace App\Http\Requests\Api;

use App\Rules\ProductLink;
use App\Support\ProductLinkNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('product_link')) {
            $this->merge([
                'product_link' => ProductLinkNormalizer::normalize($this->string('product_link')->toString()),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'product_link' => ['required', 'string', 'max:2000', new ProductLink],
            'product_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
